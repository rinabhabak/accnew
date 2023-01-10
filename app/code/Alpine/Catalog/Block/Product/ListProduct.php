<?php
/**
 * Override for Magento_Catalog ListProduct block
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc
 * @author      Aleksandr Mikhailov <aleksandr.mikhailov@alpineinc.com>
 * @author      alex.didenko@alpineinc.com
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */

namespace Alpine\Catalog\Block\Product;

use Alpine\Catalog\Block\Product\ProductList\AvailableProductsToolbar;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ProductResourceFactory;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\Helper\Data;
use Magento\ConfigurableProduct\Helper\Data as ConfigurableHelper;
use Magento\Catalog\Helper\Product as CatalogHelper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\Format;

/**
 * Class ListProduct
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * Stored Products Collection that already configured by layered navigation but not loaded yet
     *
     * @var string
     */
    const STORED_PRODUCT_COLLECTION_INSTANCE = 'alpine_catalog_initial_product_collection';

    /**
     * Product Resource Factory
     *
     * @var ProductResourceFactory
     */
    protected $productResourceFactory;

    /**
     * Configurable Helper
     *
     * @var ConfigurableHelper
     */
    protected $configurableHelper;

    /**
     * Catalog Helper
     *
     * @var CatalogHelper
     */
    protected $catalogHelper;

    /**
     * Json Encoder
     *
     * @var EncoderInterface
     */
    protected $jsonEncoder;
    
    /**
     * Configurable config
     *
     * @var array
     */
    protected $spConfig = [];
    
    /**
     * Price configs
     *
     * @var string
     */
    protected $priceConfigs = '';

    /**
     * Available Products Collection
     *
     * @var Collection
     */
    protected $availableProductsCollection;

    /**
     * Other Products Collection
     *
     * @var Collection
     */
    protected $otherProductsCollection;
    
    /**
     * Locale format
     *
     * @var Format
     */
    protected $localeFormat;

    /**
     * ListProduct constructor.
     *
     * @param Context                     $context
     * @param PostHelper                  $postDataHelper
     * @param Resolver                    $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data                        $urlHelper
     * @param ProductResourceFactory      $productResourceFactory
     * @param ConfigurableHelper          $configurableHelper
     * @param CatalogHelper               $catalogHelper
     * @param EncoderInterface            $jsonEncoder
     * @param Format                      $localeFormat
     * @param array                       $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        ProductResourceFactory $productResourceFactory,
        ConfigurableHelper $configurableHelper,
        CatalogHelper $catalogHelper,
        EncoderInterface $jsonEncoder,
        Format $localeFormat,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );

        $this->productResourceFactory = $productResourceFactory;
        $this->configurableHelper = $configurableHelper;
        $this->catalogHelper = $catalogHelper;
        $this->jsonEncoder = $jsonEncoder;
        $this->localeFormat = $localeFormat;
    }

    /**
     * Retrieve loaded product collection
     *
     * The goal of this method is to choose whether the existing collection should be returned
     * or a new one should be initialized.
     *
     * It is not just a caching logic, but also is a real logical check
     * because there are two ways how collection may be stored inside the block:
     *   - Product collection may be passed externally by 'setCollection' method
     *   - Product collection may be requested internally from the current Catalog Layer.
     *
     * And this method will return collection anyway,
     * even when it did not pass externally and therefore isn't cached yet
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $this->_productCollection = $this->initializeProductCollection();
        }

        return $this->_productCollection;
    }

    /**
     * Configures product collection from a layer and returns its instance.
     *
     * Also in the scope of a product collection configuration, this method initiates configuration of Toolbar.
     * The reason to do this is because we have a bunch of legacy code
     * where Toolbar configures several options of a collection and therefore this block depends on the Toolbar.
     *
     * This dependency leads to a situation where Toolbar sometimes called to configure a product collection,
     * and sometimes not.
     *
     * To unify this behavior and prevent potential bugs this dependency is explicitly called
     * when product collection initialized.
     *
     * @return Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function initializeProductCollection()
    {
        $layer = $this->getLayer();
        /* @var $layer \Magento\Catalog\Model\Layer */
        if ($this->getShowRootCategory()) {
            $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
        }

        // if this is a product view page
        if ($this->_coreRegistry->registry('product')) {
            // get collection of categories this product is associated with
            $categories = $this->_coreRegistry->registry('product')
                ->getCategoryCollection()->setPage(1, 1)
                ->load();
            // if the product is associated with any category
            if ($categories->count()) {
                // show products from this category
                $this->setCategoryId(current($categories->getIterator())->getId());
            }
        }

        $origCategory = null;
        if ($this->getCategoryId()) {
            try {
                $category = $this->categoryRepository->get($this->getCategoryId());
            } catch (NoSuchEntityException $e) {
                $category = null;
            }

            if ($category) {
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
            }
        }
        $collection = $layer->getProductCollection();
        // store the collection that already configured by layered navigation but not loaded yet
        $this->_coreRegistry->register(self::STORED_PRODUCT_COLLECTION_INSTANCE, clone $collection, true);

        $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        $toolbar = $this->getToolbarBlock();
        $this->configureToolbar($toolbar, $collection);

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        return $collection;
    }

    /**
     * Get "Available Online" Products
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOnlineProducts()
    {
        $collection = $this->getAvailableProductsCollection();

        $products = [];
        /* @var Product $product */
        foreach ($collection as $product) {
            $products[] = $product;
            if ($product->getTypeId() == 'configurable') {
                if (!isset($this->spConfig['add-to-cart-url'])) {
                    $children                          = $product->getTypeInstance()->getUsedProducts($product);
                    $this->spConfig['add-to-cart-url'] = $this->getAddToCartUrl(array_shift($children));
                }
                
                $allowedProducts = $this->getAllowProducts($product);
                $productId = $product->getId();
                $options = $this->configurableHelper->getOptions($product, $allowedProducts);
                $this->spConfig[$productId] = $options['index'] ?? [];
                $this->spConfig[$productId]['optionPrices'] = $this->getOptionPrices($allowedProducts);
                $this->spConfig[$productId]['count'] = $options['count'] ?? [];
            }
        }
        
        $this->setPriceConfigs($collection);

        return $products;
    }

    /**
     * Get Collection of "Other Products"
     *
     * @return Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOtherProducts()
    {
        $collection = $this->getOtherProductsCollection();

        return $collection;
    }

    /**
     * Get Available Products Collection
     *
     * @return Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAvailableProductsCollection()
    {
        if ($this->availableProductsCollection === null) {
            $this->availableProductsCollection = $this->initializeAvailableProductsCollection();
        }

        return $this->availableProductsCollection;
    }

    /**
     * Get Other Products Collection
     *
     * @return Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOtherProductsCollection()
    {
        if ($this->otherProductsCollection === null) {
            $this->otherProductsCollection = $this->initializeOtherProductsCollection();
        }

        return $this->otherProductsCollection;
    }

    /**
     * Configures product collection for "Available Products" part from a layer and returns its instance.
     *
     * @see \Alpine\Catalog\Block\Product\ListProduct::initializeProductCollection()
     *
     * @return Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initializeAvailableProductsCollection()
    {
        $layer = $this->getLayer();
        /* @var $layer \Magento\Catalog\Model\Layer */
        if ($this->getShowRootCategory()) {
            $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
        }

        // if this is a product view page
        if ($this->_coreRegistry->registry('product')) {
            // get collection of categories this product is associated with
            $categories = $this->_coreRegistry->registry('product')
                ->getCategoryCollection()->setPage(1, 1)
                ->load();
            // if the product is associated with any category
            if ($categories->count()) {
                // show products from this category
                $this->setCategoryId(current($categories->getIterator())->getId());
            }
        }

        $origCategory = null;
        if ($this->getCategoryId()) {
            try {
                $category = $this->categoryRepository->get($this->getCategoryId());
            } catch (NoSuchEntityException $e) {
                $category = null;
            }

            if ($category) {
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
            }
        }

        // Retrieve previously stored collection that already configured by layered navigation but not loaded yet
        $collection = $this->_coreRegistry->registry(self::STORED_PRODUCT_COLLECTION_INSTANCE);
        if (is_null($collection)) {
            $collection = $layer->getProductCollection();
        } else {
            $collection = clone $collection;
        }
        $collection->addAttributeToFilter('product_for_sales', ['eq' => 1])
            ->addAttributeToSelect('uom');

        $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        $toolbar = $this->getAvailableProductsToolbarBlock();
        $this->configureToolbar($toolbar, $collection);

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        return $collection;
    }

    /**
     * Retrieve Toolbar block for "Available Products"
     *
     * @return Toolbar
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAvailableProductsToolbarBlock()
    {
        $blockName = $this->getAvailableProductsToolbarBlockName();
        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock(AvailableProductsToolbar::class, uniqid(microtime()));
        return $block;
    }

    /**
     * Configures product collection for "Other Products" part from a layer and returns its instance.
     * @see \Alpine\Catalog\Block\Product\ListProduct::initializeProductCollection()
     *
     * @return Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initializeOtherProductsCollection()
    {
        $layer = $this->getLayer();
        /* @var $layer \Magento\Catalog\Model\Layer */
        if ($this->getShowRootCategory()) {
            $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
        }

        // if this is a product view page
        if ($this->_coreRegistry->registry('product')) {
            // get collection of categories this product is associated with
            $categories = $this->_coreRegistry->registry('product')
                ->getCategoryCollection()->setPage(1, 1)
                ->load();
            // if the product is associated with any category
            if ($categories->count()) {
                // show products from this category
                $this->setCategoryId(current($categories->getIterator())->getId());
            }
        }

        $origCategory = null;
        if ($this->getCategoryId()) {
            try {
                $category = $this->categoryRepository->get($this->getCategoryId());
            } catch (NoSuchEntityException $e) {
                $category = null;
            }

            if ($category) {
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
            }
        }

        // Retrieve previously stored collection that already configured by layered navigation but not loaded yet
        $collection = $this->_coreRegistry->registry(self::STORED_PRODUCT_COLLECTION_INSTANCE);
        if (is_null($collection)) {
            $collection = $layer->getProductCollection();
        } else {
            $collection = clone $collection;
        }
        $collection->addAttributeToFilter(
            'product_for_sales',
            [['eq' => 0], ['null' => true]],
            'left'
        );

        $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        $toolbar = $this->getOtherProductsToolbarBlock();
        $this->configureOtherProductsToolbar($toolbar, $collection);

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        return $collection;
    }

    /**
     * Retrieve Toolbar block for "Other Products"
     *
     * @return Toolbar
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOtherProductsToolbarBlock()
    {
        $blockName = $this->getOtherProductsToolbarBlockName();
        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock(AvailableProductsToolbar::class, uniqid(microtime()));
        return $block;
    }

    /**
     * Configures the Toolbar block with options from this block and configured product collection.
     *
     * The purpose of this method is the one-way sharing of different sorting related data
     * between this block, which is responsible for product list rendering,
     * and the Toolbar block, whose responsibility is a rendering of these options.
     *
     * @param Toolbar    $toolbar
     * @param Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function configureToolbar(Toolbar $toolbar, Collection $collection)
    {
        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }
        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);
    }

    /**
     * Configures the Toolbar block with options from this block and configured product collection.
     *
     * The purpose of this method is the one-way sharing of different sorting related data
     * between this block, which is responsible for product list rendering,
     * and the Toolbar block, whose responsibility is a rendering of these options.
     *
     * @param Toolbar    $toolbar
     * @param Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function configureOtherProductsToolbar(Toolbar $toolbar, Collection $collection)
    {
        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }
        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('o_toolbar', $toolbar);
    }

    /**
     * Retrieve list toolbar HTML for "Other Products" Section
     *
     * @return string
     */
    public function getOtherProductsToolbarHtml()
    {
        return $this->getChildHtml('o_toolbar');
    }

    /**
     * Get option code of attribute by its label
     *
     * @param $attributeCode
     * @param $label
     *
     * @return string|null
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributeOptionCode($attributeCode, $label)
    {
        $productResource = $this->productResourceFactory->create();
        $attribute = $productResource->getAttribute($attributeCode);
        $optionId = null;
        if ($attribute->usesSource()) {
            $optionId = $attribute->getSource()->getOptionId($label);
        }
        return $optionId;
    }
    
    /**
     * Get default qty - either as preconfigured, or as 1.
     * Also restricts it by minimal qty.
     *
     * @param Product $product
     * @return int|float
     */
    public function getProductDefaultQty($product)
    {
        $qty = $this->getMinimalQty($product);
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }

        return $qty;
    }
    
    /**
     * Gets max sales quantity
     *
     * @param Product $product
     * @return int|null
     */
    protected function getMaxQty($product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $maxSaleQty = $stockItem->getMaxSaleQty();
        
        return $maxSaleQty > 0 ? $maxSaleQty : null;
    }
    
    /**
     * Get Validation Rules for Quantity field
     *
     * @return array
     */
    public function getQuantityValidators()
    {
        $validators = [];
        $validators['required-number'] = true;
        
        return $validators;
    }

    /**
     * Get allow products
     *
     * @param Product $product
     * @return array
     */
    protected function getAllowProducts($product)
    {
        return $product->getTypeInstance()->getUsedProducts($product, null);
    }
    
    /**
     * Get config mapper of configurable attributes
     *
     * @return string|null
     */
    public function getSpConfig()
    {
        $result = null;

        try {
            $result = $this->jsonEncoder->encode($this->spConfig);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage(), $this->spConfig);
        } finally {
            if (!$result) {
                $result = $this->jsonEncoder->encode([]);
            }
        }

        return $result;
    }
    
    /**
     * Get configurable option prices
     *
     * @param array $products
     * @return array
     */
    protected function getOptionPrices($products)
    {
        $prices = [];
        
        foreach ($products as $product) {
            $tierPrices = [];
            $priceInfo = $product->getPriceInfo();
            $tierPriceModel =  $priceInfo->getPrice('tier_price');
            $tierPricesList = $tierPriceModel->getTierPriceList();
            foreach ($tierPricesList as $tierPrice) {
                $tierPrices[] = [
                    'qty' => $this->localeFormat->getNumber($tierPrice['price_qty']),
                    'price' => $this->localeFormat->getNumber($tierPrice['price']->getValue()),
                    'percentage' => $this->localeFormat->getNumber(
                        $tierPriceModel->getSavePercent($tierPrice['price'])
                    ),
                ];
            }

            $prices[$product->getId()] =
                [
                    'oldPrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $priceInfo->getPrice('regular_price')->getAmount()->getValue()
                        ),
                    ],
                    'basePrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $priceInfo->getPrice('final_price')->getAmount()->getBaseAmount()
                        ),
                    ],
                    'finalPrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $priceInfo->getPrice('final_price')->getAmount()->getValue()
                        ),
                    ],
                    'tierPrices' => $tierPrices,
                 ];
        }
        
        return $prices;
    }
    
    /**
     * Set price configs
     *
     * @param array $products
     */
    protected function setPriceConfigs($products)
    {
        $config = [];
        foreach ($products as $product) {
            $productId = $product->getId();
            $priceFormat = $this->localeFormat->getPriceFormat();
            if (!$product->getTypeInstance()->hasOptions($product)) {
                $config[$productId] = [
                    'productId'   => $productId,
                    'priceFormat' => $priceFormat
                ];
                continue;
            }

            $tierPrices = [];
            $priceInfo = $product->getPriceInfo();
            $tierPricesList = $priceInfo->getPrice('tier_price')->getTierPriceList();
            
            foreach ($tierPricesList as $tierPrice) {
                $tierPrices[] = $tierPrice['price']->getValue();
            }
            
            $config[$productId] = [
                'productId'   => $productId,
                'priceFormat' => $priceFormat,
                'prices'      => [
                    'oldPrice'   => [
                        'amount'      => $priceInfo->getPrice('regular_price')->getAmount()->getValue(),
                        'adjustments' => []
                    ],
                    'basePrice'  => [
                        'amount'      => $priceInfo->getPrice('final_price')->getAmount()->getBaseAmount(),
                        'adjustments' => []
                    ],
                    'finalPrice' => [
                        'amount'      => $priceInfo->getPrice('final_price')->getAmount()->getValue(),
                        'adjustments' => []
                    ]
                ],
                'idSuffix'    => '_clone',
                'tierPrices'  => $tierPrices
            ];
        }

        $this->priceConfigs = $this->jsonEncoder->encode($config);
    }
    
    /**
     * Get price configs
     *
     * @return string
     */
    public function getPriceConfigs()
    {
        return $this->priceConfigs;
    }
}
