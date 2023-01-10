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
use Exception;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductData implements ResolverInterface
{
    /**
     * Const
     */
    const PRODUCT_STOCK_ALERT = 'product_stock_alert';
    const PRODUCT_STOCK_STATUS = 'product_stock_status';
    const HAS_EMAIL_SUBSCRIBED = 'has_email_subscribed';
    const PRODUCT_ID = 'product_id';
    const PARENT_ID = 'parent_id';
    const PRODUCT_TYPE = 'product_type';
    const CUSTOMER_EMAIL = 'customer_email';

    /**
     * @var MultiSourceInventory
     */
    protected $msiHelper;

    /**
     * @var StockFactory
     */
    protected $stockFactory;

    /**
     * @var Stock
     */
    protected $stockResource;

    /**
     * @var Status
     */
    protected $stockStatusResource;

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
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * ProductData constructor.
     * @param MultiSourceInventory $multiSourceInventory
     * @param StockFactory $stockFactory
     * @param Stock $stockResource
     * @param Status $stockStatus
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        MultiSourceInventory $multiSourceInventory,
        StockFactory $stockFactory,
        Stock $stockResource,
        Status $stockStatus,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ValueFactory $valueFactory
    ) {
        $this->msiHelper = $multiSourceInventory;
        $this->stockFactory = $stockFactory;
        $this->stockResource = $stockResource;
        $this->stockStatusResource = $stockStatus;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        $currentUserId = $context->getUserId();
        /**
         * @var ContextInterface $context
         */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The request is allowed for logged in customer'));
        }
        if (!isset($args['product_id'])) {
            throw new GraphQlInputException(__('Product ID is required'));
        }
        if (!isset($args['website_id'])) {
            throw new GraphQlInputException(__('Website ID is required'));
        }
        $productId = $args['product_id'];
        $websiteId = $args['website_id'];

        $productData = $this->getProductData($productId, $websiteId, $currentUserId);

        return $this->valueFactory->create(
            function () use ($productData) {
                if (is_array($productData) &&
                    !isset($productData['product_stock_alert'])) {
                    return ['product_data' => $productData];
                }
                return ['product_data' => [$productData]];
            }
        );
    }

    /**
     * @param int $productId
     * @param int $websiteId
     * @param int $customerId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductData($productId, $websiteId, $customerId): array
    {
        try {
            $product = $this->productRepository->getById($productId);
            $customer = $this->customerRepository->getById($customerId);

            $website = $this->storeManager->getWebsite($websiteId);
            $stockResolver = $this->msiHelper->getStockResolverObject();
            $salableQty = $this->msiHelper->getSalableQtyObject();
            $stockId = $this->getStockId($website->getId(), $stockResolver, $salableQty);
            $productType = $product->getTypeId();
            $result = [];

            if ($this->checkProductType($productType, 'simple')) {
                $result = $this->buildSimple($product, $customer, $stockId, $website->getId(), $salableQty);
            } elseif ($this->checkProductType($productType, 'configurable')) {
                $result = $this->buildConfigurable($product, $customer, $stockId, $website->getId(), $salableQty);
            } elseif ($this->checkProductType($productType, 'grouped')) {
                $result = $this->buildGrouped($product, $customer, $stockId, $website->getId(), $salableQty);
            } elseif ($this->checkProductType($productType, 'bundle')) {
                $result = $this->buildBundle($product, $customer, $stockId, $website->getId(), $salableQty);
            }
            return $result;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__('We can not find data match with product ID = %1, website ID = %2', $productId, $websiteId));
        }
    }

    /**
     * @param int $websiteId
     * @param null|StockResolverInterface $stockResolver
     * @param null|GetProductSalableQtyInterface $salableQty
     * @return int|null
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
     * @param string $type
     * @param string $compareType
     * @return bool
     */
    protected function checkProductType($type, $compareType): bool
    {
        if ($compareType == "simple") {
            return in_array($type, ['simple', 'virtual', 'downloadable']);
        }
        return $type == $compareType;
    }

    /**
     * @param ProductInterface $product
     * @param CustomerInterface $customer
     * @param int $stockId
     * @param int $websiteId
     * @param GetProductSalableQtyInterface|null $salableQty
     * @return array
     * @throws LocalizedException
     */
    public function buildSimple($product, $customer, $stockId, $websiteId, $salableQty): array
    {
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $isInStock = $this->isInStock(
            $product->getSku(),
            $stockItem->getIsInStock(),
            $stockId,
            $salableQty
        );
        if (!$isInStock && $this->isProductEnabledNotice($product)) {
            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $websiteId
            );
            return [
                self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($product),
                self::PRODUCT_STOCK_STATUS => false,
                self::PRODUCT_ID => $product->getId(),
                self::PARENT_ID => $product->getId(),
                self::PRODUCT_TYPE => 'simple',
                self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                self::CUSTOMER_EMAIL => $customer->getEmail(),
            ];
        }
        return [];
    }

    /**
     * @param string $sku
     * @param bool $childStock
     * @param int $stockId
     * @param GetProductSalableQtyInterface|null $salableQty
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
     * @param ProductInterface $product
     */
    protected function isProductEnabledNotice($product): bool
    {
        if ($product->getCustomAttribute('product_stock_alert')) {
            return (bool)$product->getCustomAttribute('product_stock_alert')->getValue();
        }
        return true;
    }

    /**
     * @param ProductInterface $product
     * @param CustomerInterface $customer
     * @param int $stockId
     * @param int $websiteId
     * @param GetProductSalableQtyInterface|null $salableQty
     * @return array
     * @throws LocalizedException
     */
    public function buildConfigurable($product, $customer, $stockId, $websiteId, $salableQty): array
    {
        if (!$product->isAvailable() && $this->isProductEnabledNotice($product)) {
            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $websiteId
            );
            return [
                self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($product),
                self::PRODUCT_STOCK_STATUS => false,
                self::PRODUCT_ID => $product->getId(),
                self::PARENT_ID => $product->getId(),
                self::PRODUCT_TYPE => 'configurable',
                self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                self::CUSTOMER_EMAIL => $customer->getEmail(),
            ];
        }
        /** @var Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $childItems = $productTypeInstance->getUsedProductCollection($product);
        $childItems->addAttributeToSelect('product_stock_alert');
        $this->stockStatusResource->addStockDataToCollection($childItems, false);
        $renderData = [];
        foreach ($childItems as $childItem) {
            $isInStock = $this->isInStock(
                $childItem->getSku(),
                $childItem->getIsSalable(),
                $stockId,
                $salableQty
            );
            if (!$isInStock &&
                $this->isProductEnabledNotice($childItem)) {
                $hasEmail = $this->stockResource->hasEmail(
                    $customer->getId(),
                    $childItem['entity_id'],
                    $websiteId
                );
                $renderData[] = [
                    self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($childItem),
                    self::PRODUCT_STOCK_STATUS => false,
                    self::PRODUCT_ID => $childItem['entity_id'],
                    self::PARENT_ID => $product->getId(),
                    self::PRODUCT_TYPE => 'configurable',
                    self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                    self::CUSTOMER_EMAIL => $customer->getEmail(),
                ];
            }
        }
        return $renderData;
    }

    /**
     * @param ProductInterface $product
     * @param CustomerInterface $customer
     * @param int $stockId
     * @param int $websiteId
     * @param GetProductSalableQtyInterface|null $salableQty
     * @return array
     * @throws LocalizedException
     */
    public function buildGrouped($product, $customer, $stockId, $websiteId, $salableQty): array
    {
        if (!$product->isAvailable() && $this->isProductEnabledNotice($product)) {
            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $websiteId
            );
            return [
                self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($product),
                self::PRODUCT_STOCK_STATUS => false,
                self::PRODUCT_ID => $product->getId(),
                self::PARENT_ID => $product->getId(),
                self::PRODUCT_TYPE => 'grouped',
                self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                self::CUSTOMER_EMAIL => $customer->getEmail(),
            ];
        }
        /** @var Grouped $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $childItems = $productTypeInstance->getAssociatedProductCollection($product);
        $childItems->addAttributeToSelect(
            'product_stock_alert'
        );
        $renderData = [];
        foreach ($childItems as $childItem) {
            $isInStock = $this->isInStock(
                $childItem->getSku(),
                $childItem->getIsSalable(),
                $stockId,
                $salableQty
            );
            if (!$isInStock && $this->isProductEnabledNotice($childItem)) {
                $hasEmail = $this->stockResource->hasEmail(
                    $customer->getId(),
                    $childItem->getId(),
                    $websiteId
                );
                $renderData[] = [
                    self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($childItem),
                    self::PRODUCT_STOCK_STATUS => false,
                    self::PRODUCT_ID => $childItem->getId(),
                    self::PARENT_ID => $product->getId(),
                    self::PRODUCT_TYPE => 'grouped',
                    self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                    self::CUSTOMER_EMAIL => $customer->getEmail(),
                ];
            }
        }
        return $renderData;
    }

    /**
     * @param ProductInterface $product
     * @param CustomerInterface $customer
     * @param int $stockId
     * @param int $websiteId
     * @param GetProductSalableQtyInterface|null $salableQty
     * @return array
     * @throws LocalizedException
     */
    public function buildBundle($product, $customer, $stockId, $websiteId, $salableQty): array
    {
        if (!$product->getExtensionAttributes()->getStockItem()->getIsInStock() &&
            $this->isProductEnabledNotice($product)) {
            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $websiteId
            );
            return [
                self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($product),
                self::PRODUCT_STOCK_STATUS => false,
                self::PRODUCT_ID => $product->getId(),
                self::PARENT_ID => $product->getId(),
                self::PRODUCT_TYPE => 'bundle',
                self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                self::CUSTOMER_EMAIL => $customer->getEmail(),
            ];
        }
        /** @var Type $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter(
            $product->getStoreId(),
            $product
        );
        $selectionItems = $productTypeInstance->getSelectionsCollection(
            $productTypeInstance->getOptionsIds($product),
            $product
        )->addFieldToSelect(
            'product_id'
        )->addFieldToSelect(
            'option_id'
        )->addFieldToSelect(
            'selection_id'
        )->addAttributeToSelect(
            'product_stock_alert'
        );
        $selectionItems->getSelect()->joinInner(
            ['bundleOption' => $selectionItems->getTable('catalog_product_bundle_option')],
            'selection.option_id = bundleOption.option_id',
            ['type']
        );
        $renderData = [];
        foreach ($selectionItems as $childItem) {
            $isInStock = $this->isInStock(
                $childItem->getSku(),
                $childItem->getIsSalable(),
                $stockId,
                $salableQty
            );
            if (!$isInStock && $this->isProductEnabledNotice($childItem)) {
                $hasEmail = $this->stockResource->hasEmail(
                    $customer->getId(),
                    $childItem->getId(),
                    $websiteId
                );

                $renderData[] = [
                    self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($childItem),
                    self::PRODUCT_STOCK_STATUS => false,
                    self::PRODUCT_ID => $childItem->getId(),
                    self::PARENT_ID => $product->getId(),
                    self::PRODUCT_TYPE => 'bundle',
                    self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                    self::CUSTOMER_EMAIL => $customer->getEmail(),
                ];
            }
        }
        return $renderData;
    }
}
