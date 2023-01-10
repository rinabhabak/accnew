<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlertGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ProductStockAlertGraphQl\Model\Resolver;

use Bss\ProductStockAlert\Helper\MultiSourceInventory;
use Bss\ProductStockAlert\Model\ResourceModel\Stock;
use Bss\ProductStockAlert\Model\StockFactory;
use Bss\ProductStockAlertGraphQl\Model\Product\Validate as ProductValidate;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerialize;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Subscribe implements ResolverInterface
{
    /**
     * @var MultiSourceInventory
     */
    protected $msiHelper;

    /**
     * @var StockFactory
     */
    protected $stockFactory;

    /**
     * @var ProductValidate
     */
    protected $productValidate;

    /**
     * @var Stock
     */
    protected $stockResource;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var JsonSerialize
     */
    protected $jsonSerialize;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * Subscribe constructor.
     * @param MultiSourceInventory $multiSourceInventory
     * @param StockFactory $stockFactory
     * @param ProductValidate $productValidate
     * @param Stock $stockResource
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param JsonSerialize $jsonSerialize
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        MultiSourceInventory $multiSourceInventory,
        StockFactory $stockFactory,
        ProductValidate $productValidate,
        Stock $stockResource,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        JsonSerialize $jsonSerialize,
        ValueFactory $valueFactory
    ) {
        $this->msiHelper = $multiSourceInventory;
        $this->stockFactory = $stockFactory;
        $this->productValidate = $productValidate;
        $this->stockResource = $stockResource;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->jsonSerialize = $jsonSerialize;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value
     * @throws GraphQlAuthorizationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        $resultData = [];
        $currentUserId = $context->getUserId();
        /**
         * @var \Magento\GraphQl\Model\Query\ContextInterface $context
         */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The request is allowed for logged in customer'));
        }
        $this->validate($args, $resultData);
        $productId = $args['product_id'];
        $parentId = $args['parent_id'];
        $email = $args['email'];
        $websiteId = $args['website_id'];

        if ($email &&
            strlen($email) > 1 &&
            !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $resultData[] = [
                'message' => 'Please correct the email address: %EMAIL',
                'params' => $this->jsonSerialize->serialize([
                    'EMAIL' => $email
                ])
            ];
        }

        $dataRender = $this->subscribeStockNotice(
            $productId,
            $parentId,
            $websiteId,
            $email,
            $currentUserId,
            $resultData
        );
        return $this->valueFactory->create(
            function () use ($dataRender) {
                return $dataRender;
            }
        );
    }

    /**
     * @param array $args
     * @param array $resultData
     */
    protected function validate(array $args, array &$resultData): void
    {
        if (!isset($args['product_id'])) {
            $resultData[] = [
                'message' => 'Product ID is required',
                'params' => ''
            ];
        }
        if (!isset($args['parent_id'])) {
            $resultData[] = [
                'message' => 'Parent ID is required',
                'params' => ''
            ];
        }
        if (!isset($args['website_id'])) {
            $resultData[] = [
                'message' => 'Website ID is required',
                'params' => ''
            ];
        }
    }

    /**
     * @param $productId
     * @param $parentId
     * @param $websiteId
     * @param $email
     * @param $customerId
     * @param $resultData
     * @return array
     */
    public function subscribeStockNotice(
        $productId,
        $parentId,
        $websiteId,
        $email,
        $customerId,
        $resultData
    ): array {
        try {
            $product = $this->productRepository->getById($productId);
            $customer = $this->customerRepository->getById($customerId);
            $parent = $this->productRepository->getById($parentId);
            $website = $this->storeManager->getWebsite($websiteId);

            if (!$email || strlen($email) === 0 || $email === '') {
                $email = $customer->getEmail();
            }

            if (!$this->productValidate->validateChildProduct($product, $parent)) {
                $resultData[] = [
                    'message' => 'Product ID %PRODUCT is not child of product ID %PARENT_ID',
                    'params' => $this->jsonSerialize->serialize([
                        'PRODUCT' => $product->getId(),
                        'PARENT_ID' => $parent->getId()
                    ])
                ];
            }

            $stockResolver = $this->msiHelper->getStockResolverObject();
            $salableQty = $this->msiHelper->getSalableQtyObject();
            $stockId = $this->getStockId($websiteId, $stockResolver, $salableQty);
            $isInStock = $this->isInStock(
                $product->getSku(),
                $product->getIsSalable(),
                $stockId,
                $salableQty
            );
            if ($isInStock || !$this->isProductEnabledNotice($product)) {
                $resultData[] = [
                    'message' => 'Product with sku %SKU in website %WEBSITE ' .
                        'is not allow to subscribe a stock notice right now.',
                    'params' => $this->jsonSerialize->serialize([
                        'SKU' => $product->getSku(),
                        'WEBSITE' => $website->getCode()
                    ])
                ];
            }

            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $websiteId
            );

            if ($hasEmail) {
                $resultData[] = [
                    'message' => 'You already subscribed for product %SKU in website %WEBSITE.',
                    'params' => $this->jsonSerialize->serialize([
                        'SKU' => $product->getSku(),
                        'WEBSITE' => $website->getCode()
                    ])
                ];
            }

            if (empty($resultData)) {
                $model = $this->stockFactory->create()
                    ->setCustomerId($customer->getId())
                    ->setCustomerEmail($email)
                    ->setCustomerName($customer->getFirstname() . " " . $customer->getLastname())
                    ->setProductSku($product->getSku())
                    ->setProductId($product->getId())
                    ->setWebsiteId(
                        $websiteId
                    )
                    ->setStoreId(
                        $website->getDefaultStore()->getId()
                    )
                    ->setParentId($parent->getId());
                $model->save();
            }
        } catch (Exception $exception) {
            $resultData[] = [
                'message' => $exception->getMessage(),
                'params' => ''
            ];
        }
        return $resultData;
    }

    /**
     * @param int $websiteId
     * @param null|StockResolverInterface $stockResolver
     * @param null|GetProductSalableQtyInterface $salableQty
     * @return int
     */
    protected function getStockId(
        $websiteId,
        $stockResolver,
        $salableQty
    ): int {
        try {
            $wsCode = $this->storeManager->getWebsite($websiteId)->getCode();
            if ($stockResolver && $stockResolver instanceof StockResolverInterface &&
                $salableQty && $salableQty instanceof GetProductSalableQtyInterface) {
                return $stockResolver->execute('website', $wsCode)->getStockId();
            }
            return 0;
        } catch (Exception $exception) {
            return 0;
        }
    }

    /**
     * @param string $sku
     * @param bool $childStock
     * @param int $stockId
     * @param GetProductSalableQtyInterface $salableQty
     * @return bool
     */
    protected function isInStock(
        $sku,
        $childStock,
        $stockId,
        $salableQty
    ): bool {
        if (!$stockId) {
            return $childStock;
        }
        try {
            return (bool)$salableQty->execute($sku, (int)$stockId);
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    protected function isProductEnabledNotice($product): bool
    {
        if ($product->getCustomAttribute('product_stock_alert')) {
            return (bool)$product->getCustomAttribute('product_stock_alert')->getValue();
        }
        return true;
    }
}
