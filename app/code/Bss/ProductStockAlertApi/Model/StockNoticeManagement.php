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
 * @package    Bss_ProductStockAlertApi
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlertApi\Model;

use Bss\ProductStockAlertApi\Api\StockNoticeManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Bss\ProductStockAlert\Helper\Data;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class StockNoticeManagement implements StockNoticeManagementInterface
{
    /**
     * @var \Bss\ProductStockAlertApi\Api\Data\ConfigurationInterfaceFactory
     */
    protected $configurationFactory;

    /**
     * @var \Bss\ProductStockAlertApi\Api\Data\ProductDataInterfaceFactory
     */
    protected $productDataFactory;

    /**
     * @var \Bss\ProductStockAlertApi\Api\Data\StockNoticeInterfaceFactory
     */
    protected $stockNoticeFactory;

    /**
     * @var \Bss\ProductStockAlertApi\Api\Data\ResultDataInterfaceFactory
     */
    protected $resultDataFactory;

    /**
     * @var \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterfaceFactory
     */
    protected $productDataResultFactory;

    /**
     * @var \Bss\ProductStockAlertApi\Api\Data\StockNoticeResultInterfaceFactory
     */
    protected $stockNoticeResultFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Bss\ProductStockAlert\Helper\MultiSourceInventory
     */
    protected $msiHelper;

    /**
     * @var \Bss\ProductStockAlert\Model\StockFactory
     */
    protected $stockFactory;

    /**
     * @var \Bss\ProductStockAlert\Model\ResourceModel\Stock
     */
    protected $stockResource;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     */
    protected $stockStatusResource;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * StockNoticeManagement constructor.
     * @param \Bss\ProductStockAlertApi\Api\Data\ConfigurationInterfaceFactory $configurationFactory
     * @param \Bss\ProductStockAlertApi\Api\Data\ProductDataInterfaceFactory $productDataFactory
     * @param \Bss\ProductStockAlertApi\Api\Data\StockNoticeInterfaceFactory $stockNoticeFactory
     * @param \Bss\ProductStockAlertApi\Api\Data\ResultDataInterfaceFactory $resultDataFactory
     * @param \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterfaceFactory $productDataResultFactory
     * @param \Bss\ProductStockAlertApi\Api\Data\StockNoticeResultInterfaceFactory $stockNoticeResultFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventory
     * @param \Bss\ProductStockAlert\Model\StockFactory $stockFactory
     * @param \Bss\ProductStockAlert\Model\ResourceModel\Stock $stockResource
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $stockStatus
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        \Bss\ProductStockAlertApi\Api\Data\ConfigurationInterfaceFactory $configurationFactory,
        \Bss\ProductStockAlertApi\Api\Data\ProductDataInterfaceFactory $productDataFactory,
        \Bss\ProductStockAlertApi\Api\Data\StockNoticeInterfaceFactory $stockNoticeFactory,
        \Bss\ProductStockAlertApi\Api\Data\ResultDataInterfaceFactory $resultDataFactory,
        \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterfaceFactory $productDataResultFactory,
        \Bss\ProductStockAlertApi\Api\Data\StockNoticeResultInterfaceFactory $stockNoticeResultFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventory,
        \Bss\ProductStockAlert\Model\StockFactory $stockFactory,
        \Bss\ProductStockAlert\Model\ResourceModel\Stock $stockResource,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $stockStatus,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->configurationFactory = $configurationFactory;
        $this->productDataFactory = $productDataFactory;
        $this->stockNoticeFactory = $stockNoticeFactory;
        $this->resultDataFactory = $resultDataFactory;
        $this->productDataResultFactory = $productDataResultFactory;
        $this->stockNoticeResultFactory = $stockNoticeResultFactory;
        $this->scopeConfig = $scopeConfig;
        $this->msiHelper = $multiSourceInventory;
        $this->stockFactory = $stockFactory;
        $this->stockResource = $stockResource;
        $this->stockStatusResource = $stockStatus;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration($storeId)
    {
        $store = $this->storeManager->getStore($storeId);

        /** @var \Bss\ProductStockAlertApi\Api\Data\ConfigurationInterface $configuration */
        $configuration = $this->configurationFactory->create();
        $configuration->setAllowStock((bool)$this->scopeConfig->getValue(
            Data::XML_PATH_STOCK_ALLOW,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        ));
        $allowCustomer = $this->scopeConfig->getValue(
            Data::XML_PATH_CUSTOMER_ALLOW,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );

        $filter = $this->filterBuilder
            ->setValue(explode(',', $allowCustomer))
            ->setField('customer_group_id')
            ->setConditionType('in')
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter])->create();
        $customerGroups = $this->groupRepository->getList($searchCriteria);
        $customerGroupsArr = [];
        /** @var \Magento\Customer\Api\Data\GroupInterface $group */
        array_map(function ($group) use (&$customerGroupsArr) {
            $customerGroupsArr[] = $group->getCode();
        }, $customerGroups->getItems());

        $configuration->setAllowCustomer($customerGroupsArr);
        $configuration->setEmailBasedQty((bool)$this->scopeConfig->getValue(
            Data::XML_PATH_EMAIL_SEND_BASED_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        ));
        $configuration->setMessage($this->scopeConfig->getValue(
            Data::XML_PATH_NOTIFICATION_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        ));
        $configuration->setStopMessage($this->scopeConfig->getValue(
            Data::XML_PATH_STOP_NOTIFICATION_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        ));
        $configuration->setSendLimit((int)$this->scopeConfig->getValue(
            Data::XML_PATH_SEND_LIMIT,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        ));
        $configuration->setAllowStockQty((int)$this->scopeConfig->getValue(
            Data::XML_PATH_QTY_ALLOW,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        ));
        $configuration->setButtonText($this->scopeConfig->getValue(
            Data::XML_BUTTON_DESIGN_BUTTON_TEXT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        ));
        $configuration->setStopButtonText($this->scopeConfig->getValue(
            Data::XML_BUTTON_DESIGN_STOP_BUTTON_TEXT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        ));
        $configuration->setButtonTextColor($this->scopeConfig->getValue(
            Data::XML_BUTTON_DESIGN_BUTTON_TEXT_COLOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        ));
        $configuration->setButtonColor($this->scopeConfig->getValue(
            Data::XML_BUTTON_DESIGN_BUTTON_COLOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        ));
        return $configuration;
    }

    /**
     * @inheritDoc
     */
    public function getProductData($productId, $websiteId, $customerId)
    {
        $product = $this->productRepository->getById($productId);
        $customer = $this->customerRepository->getById($customerId);
        $website = $this->storeManager->getWebsite($websiteId);

        $stockResolver = $this->msiHelper->getStockResolverObject();
        $salableQty = $this->msiHelper->getSalableQtyObject();
        $stockId = $this->getStockId($website->getId(), $stockResolver, $salableQty);
        $productType = $product->getTypeId();
        /** @var \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterface $resultData */
        $resultData = $this->productDataResultFactory->create();
        if ($this->checkProductType($productType, 'simple')) {
            return $this->buildSimple($product, $customer, $stockId, $website->getId(), $salableQty, $resultData);
        } elseif ($this->checkProductType($productType, 'configurable')) {
            return $this->buildConfigurable($product, $customer, $stockId, $website->getId(), $salableQty, $resultData);
        } elseif ($this->checkProductType($productType, 'grouped')) {
            return $this->buildGrouped($product, $customer, $stockId, $website->getId(), $salableQty, $resultData);
        } elseif ($this->checkProductType($productType, 'bundle')) {
            return $this->buildBundle($product, $customer, $stockId, $website->getId(), $salableQty, $resultData);
        }
        return $resultData->setItems([]);
    }

    /**
     * @inheritDoc
     */
    public function subscribeStockNotice($productId, $parentId, $websiteId, $email, $customerId)
    {
        /** @var \Bss\ProductStockAlertApi\Api\Data\ResultDataInterface $resultData */
        $resultData = $this->resultDataFactory->create();

        try {
            $product = $this->productRepository->getById($productId);
            $customer = $this->customerRepository->getById($customerId);
            $parent = $this->productRepository->getById($parentId);
            $website = $this->storeManager->getWebsite($websiteId);
            $customerEmail = !$email || strlen($email) === 0 || $email === '' ? $customer->getEmail() : $email;
            $this->validateEmail($customerEmail, $resultData);

            if (!$this->validateProduct($product, $parent)) {
                $resultData->addItem(
                    [
                        'message' => 'Product ID %PRODUCT is not child of product ID %PARENT_ID',
                        'params' => [
                            'PRODUCT' => $product->getId(),
                            'PARENT_ID' => $parent->getId()
                        ]
                    ]
                );
            }

            $stockResolver = $this->msiHelper->getStockResolverObject();
            $salableQty = $this->msiHelper->getSalableQtyObject();
            $stockId = $this->getStockId($website->getId(), $stockResolver, $salableQty);
            $isInStock = $this->isInStock(
                $product->getSku(),
                $product->getIsSalable(),
                $stockId,
                $salableQty
            );
            if ($isInStock || !$this->isProductEnabledNotice($product)) {
                $resultData->addItem(
                    [
                        'message' => 'Product with sku %SKU in website %WEBSITE ' .
                            'does not allow to subscribe a stock notice right now.',
                        'params' => [
                            'SKU' => $product->getSku(),
                            'WEBSITE' => $website->getCode()
                        ]
                    ]
                );
            }

            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $website->getId()
            );

            if ($hasEmail) {
                $resultData->addItem(
                    [
                        'message' => 'You already subscribed for product %SKU in website %WEBSITE.',
                        'params' => [
                            'SKU' => $product->getSku(),
                            'WEBSITE' => $website->getCode()
                        ]
                    ]
                );
            }

            if (empty($resultData->getItems()) || !$resultData->getItems()) {
                $model = $this->stockFactory->create()
                    ->setCustomerId($customer->getId())
                    ->setCustomerEmail($customerEmail)
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
        } catch (\Exception $exception) {
            $resultData->addItem([
                'message' => $exception->getMessage(),
                'params' => ''
            ]);
        }
        return empty($resultData->getItems()) || !$resultData->getItems() ? $resultData->setItems([]) : $resultData;
    }

    /**
     * @param string $email
     * @param \Bss\ProductStockAlertApi\Api\Data\ResultDataInterface $resultData
     */
    protected function validateEmail($email, &$resultData)
    {
        if ($email &&
            strlen($email) &&
            !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $resultData->addItem([
                'message' => 'Please correct the email address: %EMAIL',
                'params' => [
                    'EMAIL' => $email
                ]
            ]);
        }
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param null|\Magento\Catalog\Api\Data\ProductInterface $parent
     */
    protected function validateProduct($product, $parent)
    {
        if (in_array($parent->getTypeId(), ['simple', 'virtual', 'downloadable'])) {
            return $parent->getId() === $product->getId();
        } elseif ($parent->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $childs = $parent->getTypeInstance()->getUsedProducts($parent);
            foreach ($childs as $child) {
                if ($child->getId() === $product->getId()) {
                    return true;
                }
            }
            return false;
        } elseif ($parent->getTypeId() === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            $childs = $parent->getTypeInstance()->getAssociatedProducts($parent);
            foreach ($childs as $child) {
                if ($child->getId() === $product->getId()) {
                    return true;
                }
            }
            return false;
        } elseif ($parent->getTypeId() === \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            $childIdsArr = $parent->getTypeInstance()->getChildrenIds($parent->getId(), false);
            foreach ($childIdsArr as $childIds) {
                if (in_array($product->getId(), $childIds)) {
                    return true;
                }
                continue;
            }
            return false;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function unsubscribeStockNotice($productId, $parentId, $websiteId, $customerId)
    {
        /** @var \Bss\ProductStockAlertApi\Api\Data\ResultDataInterface $resultData */
        $resultData = $this->resultDataFactory->create();

        try {
            $product = $this->productRepository->getById($productId);
            $customer = $this->customerRepository->getById($customerId);
            $parent = $this->productRepository->getById($parentId);
            $website = $this->storeManager->getWebsite($websiteId);

            if (!$this->validateProduct($product, $parent)) {
                $resultData->addItem(
                    [
                        'message' => 'Product ID %PRODUCT is not child of product ID %PARENT',
                        'params' => [
                            'PRODUCT' => $product->getId(),
                            'PARENT' => $parent->getId()
                        ]
                    ]
                );
            }

            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $website->getId()
            );

            if (!$hasEmail) {
                $resultData->addItem(
                    [
                        'message' => 'You did not subscribe on product %SKU in website %WEBSITE.',
                        'params' => [
                            'SKU' => $product->getSku(),
                            'WEBSITE' => $website->getCode()
                        ]
                    ]
                );
            }

            if (empty($resultData->getItems()) || !$resultData->getItems()) {
                $stockModel = $this->stockFactory->create()
                    ->setCustomerId($customer->getId())
                    ->setProductId($product->getId())
                    ->setWebsiteId(
                        $website->getId()
                    )->setStoreId(
                        $website->getDefaultStore()->getId()
                    )->setParentId(
                        $parent->getId()
                    )
                    ->loadByParam();

                if ($stockModel->getAlertStockId()) {
                    $stockModel->delete();
                } else {
                    $resultData->addItem(
                        [
                            'message' => 'We could not find any record match with product %SKU in website %WEBSITE.',
                            'params' => [
                                'SKU' => $product->getSku(),
                                'WEBSITE' => $website->getCode()
                            ]
                        ]
                    );
                }
            }
        } catch (\Exception $exception) {
            $resultData->addItem([
                'message' => $exception->getMessage(),
                'params' => ''
            ]);
        }
        return empty($resultData->getItems()) || !$resultData->getItems() ? $resultData->setItems([]) : $resultData;
    }

    /**
     * @inheritDoc
     */
    public function unsubscribeAllStockNotice($websiteId, $customerId)
    {
        /** @var \Bss\ProductStockAlertApi\Api\Data\ResultDataInterface $resultData */
        $resultData = $this->resultDataFactory->create();
        try {
            $customer = $this->customerRepository->getById($customerId);
            $website = $this->storeManager->getWebsite($websiteId);
            $this->stockFactory->create()
                ->deleteCustomer(
                    $customer->getId(),
                    $website->getId()
                );
        } catch (\Exception $exception) {
            $resultData->addItem([
                'message' => $exception->getMessage(),
                'params' => ''
            ]);
        }
        return empty($resultData->getItems()) || !$resultData->getItems() ? $resultData->setItems([]) : $resultData;
    }

    /**
     * @inheritDoc
     */
    public function getListByCustomer($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        /** @var \Bss\ProductStockAlertApi\Api\Data\StockNoticeResultInterface $resultData */
        $resultData = $this->stockNoticeResultFactory->create();
        $conditions = [
            'customer_id' => $customer->getId()
        ];
        $columns = \Bss\ProductStockAlertApi\Api\Data\StockNoticeInterface::COLUMNS;
        $stockList = $this->stockResource->getStockNotice($conditions, $columns);
        $renderData = [];
        foreach ($stockList as $stockItem) {
            /** @var \Bss\ProductStockAlertApi\Api\Data\StockNoticeInterface $stockNoticeTemplate */
            $stockNoticeTemplate = $this->stockNoticeFactory->create();
            $stockNoticeTemplate->addData($stockItem);
            $renderData[] = $stockNoticeTemplate;
        }
        return !empty($renderData) ? $resultData->setItems($renderData) : $resultData->setItems([]);
    }

    /**
     * @inheritDoc
     */
    public function getById($stockId, $customerId)
    {
        /** @var \Bss\ProductStockAlertApi\Api\Data\StockNoticeResultInterface $resultData */
        $resultData = $this->stockNoticeResultFactory->create();
        $customer = $this->customerRepository->getById($customerId);
        $conditions = [
            'alert_stock_id' => $stockId,
            'customer_id' => $customer->getId()
        ];
        $columns = \Bss\ProductStockAlertApi\Api\Data\StockNoticeInterface::COLUMNS;
        $stockList = $this->stockResource->getStockNotice($conditions, $columns);
        $stockItem = $stockList[array_key_first($stockList)] ?? [];
        /** @var \Bss\ProductStockAlertApi\Api\Data\StockNoticeInterface $stockNoticeTemplate */
        $stockNoticeTemplate = $this->stockNoticeFactory->create();
        $stockNoticeTemplate->addData($stockItem);
        return empty($stockItem) ? $resultData->setItems([]) : $resultData->setItems([$stockNoticeTemplate]);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int $stockId
     * @param int $websiteId
     * @param null|\Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $salableQty
     * @param \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterface $resultData
     * @return \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterface
     * @throws LocalizedException
     */
    public function buildSimple($product, $customer, $stockId, $websiteId, $salableQty, $resultData)
    {
        /** @var \Bss\ProductStockAlertApi\Api\Data\ProductDataInterface $productData */
        $productData = $this->productDataFactory->create();
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
            $this->setStockNoticeData(
                $productData,
                (bool) $this->isProductEnabledNotice($product),
                (int) $product->getId(),
                (int) $product->getId(),
                'simple',
                (bool) $hasEmail,
                $customer->getEmail()
            );
            return $resultData->setItems([$productData]);
        }
        return $resultData->setItems([]);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int $stockId
     * @param int $websiteId
     * @param null|\Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $salableQty
     * @param \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterface $resultData
     * @return \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterface
     * @throws LocalizedException
     */
    public function buildConfigurable($product, $customer, $stockId, $websiteId, $salableQty, $resultData)
    {
        if (!$product->isAvailable() && $this->isProductEnabledNotice($product)) {
            /** @var \Bss\ProductStockAlertApi\Api\Data\ProductDataInterface $productData */
            $productData = $this->productDataFactory->create();
            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $websiteId
            );
            $this->setStockNoticeData(
                $productData,
                (bool) $this->isProductEnabledNotice($product),
                (int) $product->getId(),
                (int) $product->getId(),
                'configurable',
                (bool) $hasEmail,
                $customer->getEmail()
            );
            return $resultData->setItems([$productData]);
        }
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $childItems = $productTypeInstance->getUsedProductCollection($product);
        $childItems->addAttributeToSelect('product_stock_alert');
        $this->stockStatusResource->addStockDataToCollection($childItems, false);
        $renderData = [];
        foreach ($childItems as $childItem) {
            /** @var \Bss\ProductStockAlertApi\Api\Data\ProductDataInterface $productData */
            $productData = $this->productDataFactory->create();
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
                $this->setStockNoticeData(
                    $productData,
                    (bool) $this->isProductEnabledNotice($childItem),
                    (int) $childItem['entity_id'],
                    (int) $product->getId(),
                    'configurable',
                    (bool) $hasEmail,
                    $customer->getEmail()
                );
                $renderData[] = $productData;
            }
        }
        return !empty($renderData) ? $resultData->setItems($renderData) : $resultData->setItems([]);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int $stockId
     * @param int $websiteId
     * @param null|\Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $salableQty
     * @param \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterface $resultData
     * @return \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterface
     * @throws LocalizedException
     */
    public function buildGrouped($product, $customer, $stockId, $websiteId, $salableQty, $resultData)
    {
        if (!$product->isAvailable() && $this->isProductEnabledNotice($product)) {
            /** @var \Bss\ProductStockAlertApi\Api\Data\ProductDataInterface $productData */
            $productData = $this->productDataFactory->create();
            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $websiteId
            );
            $this->setStockNoticeData(
                $productData,
                (bool) $this->isProductEnabledNotice($product),
                (int) $product->getId(),
                (int) $product->getId(),
                'grouped',
                (bool) $hasEmail,
                $customer->getEmail()
            );
            return $resultData->setItems([$productData]);
        }
        /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $productTypeInstance */
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
                /** @var \Bss\ProductStockAlertApi\Api\Data\ProductDataInterface $productData */
                $productData = $this->productDataFactory->create();
                $hasEmail = $this->stockResource->hasEmail(
                    $customer->getId(),
                    $childItem->getId(),
                    $websiteId
                );
                $this->setStockNoticeData(
                    $productData,
                    (bool) $this->isProductEnabledNotice($childItem),
                    (int) $childItem->getId(),
                    (int) $product->getId(),
                    'grouped',
                    (bool) $hasEmail,
                    $customer->getEmail()
                );
                $renderData[] = $productData;
            }
        }
        return !empty($renderData) ? $resultData->setItems($renderData) : $resultData->setItems([]);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int $stockId
     * @param int $websiteId
     * @param \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $salableQty
     * @param \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterface $resultData
     * @return \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterface
     * @throws LocalizedException
     */
    public function buildBundle($product, $customer, $stockId, $websiteId, $salableQty, $resultData)
    {
        if (!$product->getExtensionAttributes()->getStockItem()->getIsInStock() &&
            $this->isProductEnabledNotice($product)) {
            /** @var \Bss\ProductStockAlertApi\Api\Data\ProductDataInterface $productData */
            $productData = $this->productDataFactory->create();
            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $websiteId
            );
            $this->setStockNoticeData(
                $productData,
                (bool) $this->isProductEnabledNotice($product),
                (int) $product->getId(),
                (int) $product->getId(),
                'bundle',
                (bool) $hasEmail,
                $customer->getEmail()
            );
            return $resultData->setItems([$productData]);
        }
        /** @var \Magento\Bundle\Model\Product\Type $productTypeInstance */
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
                /** @var \Bss\ProductStockAlertApi\Api\Data\ProductDataInterface $productData */
                $productData = $this->productDataFactory->create();
                $hasEmail = $this->stockResource->hasEmail(
                    $customer->getId(),
                    $childItem->getId(),
                    $websiteId
                );
                $this->setStockNoticeData(
                    $productData,
                    (bool) $this->isProductEnabledNotice($childItem),
                    (int) $childItem->getId(),
                    (int) $product->getId(),
                    'bundle',
                    (bool) $hasEmail,
                    $customer->getEmail()
                );
                $renderData[] = $productData;
            }
        }
        return !empty($renderData) ? $resultData->setItems($renderData) : $resultData->setItems([]);
    }

    /**
     * @param \Bss\ProductStockAlertApi\Api\Data\ProductDataInterface $productData
     * @param bool $stockAlert
     * @param int $productId
     * @param int $parentId
     * @param string $productType
     * @param bool $hasEmail
     * @param string $customerEmail
     */
    protected function setStockNoticeData(
        $productData,
        $stockAlert,
        $productId,
        $parentId,
        $productType,
        $hasEmail,
        $customerEmail
    ) {
        $productData->setProductStockStatus(false);
        $productData->setProductStockAlert($stockAlert);
        $productData->setProductId($productId);
        $productData->setParentId($parentId);
        $productData->setProductType($productType);
        $productData->setHasEmailSubscribed($hasEmail);
        $productData->setCustomerEmail($customerEmail);
    }

    /**
     * @param int $websiteId
     * @param null|\Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver
     * @param null|\Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $salableQty
     * @return int|null
     */
    protected function getStockId(
        $websiteId,
        $stockResolver,
        $salableQty
    ) {
        try {
            $wsCode = $this->storeManager->getWebsite($websiteId)->getCode();
            if ($stockResolver && $stockResolver instanceof \Magento\InventorySalesApi\Api\StockResolverInterface &&
                $salableQty && $salableQty instanceof \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface) {
                return $stockResolver->execute('website', $wsCode)->getStockId();
            }
            return null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param string $type
     * @param string $compareType
     * @return bool
     */
    protected function checkProductType($type, $compareType)
    {
        if ($compareType == "simple") {
            return in_array($type, ['simple', 'virtual', 'downloadable']);
        }
        return $type == $compareType;
    }

    /**
     * @param string $sku
     * @param bool $childStock
     * @param int $stockId
     * @param \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $salableQty
     * @return bool
     */
    protected function isInStock(
        $sku,
        $childStock,
        $stockId,
        $salableQty
    ) {
        if (!$stockId) {
            return $childStock;
        }
        try {
            return (bool)$salableQty->execute($sku, (int)$stockId);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return string|int|bool|null
     */
    protected function isProductEnabledNotice($product)
    {
        if ($product->getCustomAttribute('product_stock_alert')) {
            return $product->getCustomAttribute('product_stock_alert')->getValue() == '1';
        }
        return true;
    }
}
