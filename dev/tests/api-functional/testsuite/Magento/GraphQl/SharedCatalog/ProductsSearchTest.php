<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SharedCatalog;

use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Search products for a specific shared catalog
 */
class ProductsSearchTest extends GraphQlAbstract
{
    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $getCustomerAuthenticationHeader;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getCustomerAuthenticationHeader = $objectManager->get(GetCustomerAuthenticationHeader::class);
    }

    /**
     * Response needs to have exact items in place with prices available
     *
     * @magentoConfigFixture default_store catalog/magento_catalogpermissions/enabled 1
     * @magentoConfigFixture default_store btob/website_configuration/company_active 1
     * @magentoConfigFixture default_store btob/website_configuration/sharedcatalog_active 1
     * @magentoApiDataFixture Magento/SharedCatalog/_files/permissions/multiple_shared_catalogs.php
     * @magentoApiDataFixture Magento/SharedCatalog/_files/permissions/companies_with_admin.php
     * @magentoApiDataFixture Magento/SharedCatalog/_files/permissions/categories.php
     */
    public function testProductsSearchWithPricesAllowed()
    {
        $this->reindexCatalogPermissions();

        $companyIdentifier = 0;
        $currentEmail = 'admin@' . $companyIdentifier . 'company.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery(
            $this->getQuery(),
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );

        //Verify exact number of products are returned
        $this->assertCount(3, $response['products']['items']);

        $items = $response['products']['items'];
        foreach ($items as $item) {
            $id = $item['id'];
            $sku = $item['sku'];
            //Verify expected sku's pattern
            $splitSku = explode("_", $sku);
            $this->assertEquals($companyIdentifier, (int) substr($splitSku[1], 0, 1));
            //Verify price is available
            $specialPrice = $item['special_price'];
            $this->assertEquals(10, (int)$id - (int)$specialPrice);
        }
    }

    /**
     * Response needs to have to have exact items in place but without prices
     *
     * @magentoConfigFixture default_store catalog/magento_catalogpermissions/enabled 1
     * @magentoConfigFixture default_store btob/website_configuration/company_active 1
     * @magentoConfigFixture default_store btob/website_configuration/sharedcatalog_active 1
     * @magentoApiDataFixture Magento/SharedCatalog/_files/permissions/multiple_shared_catalogs.php
     * @magentoApiDataFixture Magento/SharedCatalog/_files/permissions/companies_with_admin.php
     * @magentoApiDataFixture Magento/SharedCatalog/_files/permissions/categories.php
     */
    public function testProductsSearchWithPricesDenied()
    {
        $this->reindexCatalogPermissions();

        $companyIdentifier = 2;
        $currentEmail = 'admin@' . $companyIdentifier . 'company.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery(
            $this->getQuery(),
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );

        //Verify exact number of products are returned
        $this->assertCount(3, $response['products']['items']);

        $items = $response['products']['items'];
        foreach ($items as $item) {
            $sku = $item['sku'];
            //Verify expected sku's pattern
            $splitSku = explode("_", $sku);
            $this->assertEquals($companyIdentifier, (int) substr($splitSku[1], 0, 1));
            //Verify price is not available
            $priceRange = $item['price_range'];
            $this->assertNull($priceRange['minimum_price']['final_price']['value']);
            $this->assertNull($item['special_price']);
        }
    }

    /**
     * Get products search query
     *
     * @return string
     */
    private function getQuery(): string
    {
        $query = <<<QUERY
{
  products(search: "Product"){
    items {
      id
      name
      sku
      price_range {
        minimum_price {
          final_price {
            value
          }
        }
      }
      special_price
    }
    total_count
  }
}
QUERY;
        return $query;
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
