<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SharedCatalog;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\ObjectManager;

/**
 * Add products to cart from a specific shared catalog
 */
class AddProductToCartTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $getCustomerAuthenticationHeader;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getCustomerAuthenticationHeader = $this->objectManager->get(GetCustomerAuthenticationHeader::class);
    }

    /**
     * Response should have cart items available
     *
     * @magentoConfigFixture default_store catalog/magento_catalogpermissions/enabled 1
     * @magentoConfigFixture default_store btob/website_configuration/company_active 1
     * @magentoConfigFixture default_store btob/website_configuration/sharedcatalog_active 1
     * @magentoApiDataFixture Magento/SharedCatalog/_files/permissions/multiple_shared_catalogs.php
     * @magentoApiDataFixture Magento/SharedCatalog/_files/permissions/companies_with_admin.php
     * @magentoApiDataFixture Magento/SharedCatalog/_files/permissions/categories.php
     */
    public function testProductIsAddedToCart()
    {
        $this->reindexCatalogPermissions();

        $productSku = 'product_00';
        $desiredQuantity = 5;
        $currentEmail = 'admin@0company.com';
        $currentPassword = 'password';
        $headerAuthorization = $this->objectManager->get(GetCustomerAuthenticationHeader::class)
            ->execute($currentEmail, $currentPassword);
        $cartId = $this->createEmptyCart($headerAuthorization);

        $response = $this->graphQlMutation(
            $this->prepareMutation($cartId, $productSku, $desiredQuantity),
            [],
            '',
            $headerAuthorization
        );

        $this->removeQuote($cartId);

        $this->assertNotEmpty($response['addProductsToCart']['cart']['items']);
        $cartItems = $response['addProductsToCart']['cart']['items'];
        $this->assertEquals($desiredQuantity, $cartItems[0]['quantity']);
        $this->assertEquals($productSku, $cartItems[0]['product']['sku']);
    }

    /**
     * Response should have no cart items
     *
     * @magentoConfigFixture default_store catalog/magento_catalogpermissions/enabled 1
     * @magentoConfigFixture default_store btob/website_configuration/company_active 1
     * @magentoConfigFixture default_store btob/website_configuration/sharedcatalog_active 1
     * @magentoApiDataFixture Magento/SharedCatalog/_files/permissions/multiple_shared_catalogs.php
     * @magentoApiDataFixture Magento/SharedCatalog/_files/permissions/companies_with_admin.php
     * @magentoApiDataFixture Magento/SharedCatalog/_files/permissions/categories.php
     */
    public function testProductIsDeniedToCart()
    {
        $this->reindexCatalogPermissions();

        $productSku = 'product_20';
        $desiredQuantity = 5;
        $currentEmail = 'admin@2company.com';
        $currentPassword = 'password';
        $headerAuthorization = $this->objectManager->get(GetCustomerAuthenticationHeader::class)
            ->execute($currentEmail, $currentPassword);
        $cartId = $this->createEmptyCart($headerAuthorization);

        $response = $this->graphQlMutation(
            $this->prepareMutation($cartId, $productSku, $desiredQuantity),
            [],
            '',
            $headerAuthorization
        );

        $this->removeQuote($cartId);

        $this->assertEmpty($response['addProductsToCart']['cart']['items']);
    }

    /**
     * Prepare add products to cart mutation
     *
     * @param string $cartId
     * @param string $productSku
     * @param int $desiredQuantity
     * @return string
     */
    private function prepareMutation(string $cartId, string $productSku, int $desiredQuantity): string
    {
        $mutation = <<<MUTATION
mutation {
  addProductsToCart(
    cartId: "{$cartId}",
    cartItems: [
      {
          sku: "{$productSku}"
          quantity: {$desiredQuantity}
      }

      ]
  ) {
    cart {
      items {
       quantity
       product {
          sku
        }
      }
    }
  }
}
MUTATION;
        return $mutation;
    }

    /**
     * Create empty cart
     *
     * @return string
     * @throws \Exception
     */
    private function createEmptyCart(array $headerAuthorization): string
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $headerAuthorization
        );
        $cartId = $response['createEmptyCart'];
        return $cartId;
    }

    /**
     * Remove the quote
     *
     * @param string $maskedId
     */
    private function removeQuote(string $maskedId): void
    {
        $maskedIdToQuote = $this->objectManager->get(MaskedQuoteIdToQuoteIdInterface::class);
        $quoteId = $maskedIdToQuote->execute($maskedId);

        $cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $quote = $cartRepository->get($quoteId);
        $cartRepository->delete($quote);
    }

    /**
     * Reindex catalog permissions
     */
    private function reindexCatalogPermissions()
    {
        $appDir = dirname(Bootstrap::getInstance()->getAppTempDir());
        $out = '';
        // phpcs:ignore Magento2.Security.InsecureFunction
        exec("php -f {$appDir}/bin/magento indexer:reindex catalogpermissions_category", $out);
    }
}
