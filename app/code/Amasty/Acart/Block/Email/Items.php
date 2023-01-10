<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Block\Email;

use Amasty\Acart\Model\ConfigProvider;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Helper\Product\Configuration;
use Amasty\Acart\Model\UrlManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Bundle\Model\Product\Type;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class Items extends \Magento\Framework\View\Element\Template
{
    const FIRST_PARENT_PRODUCT = 0;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var Type
     */
    private $bundleType;

    /**
     * @var Grouped
     */
    private $groupedType;

    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @var Configuration
     */
    protected $_productConfig;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var array
     */
    protected $_params = [
        'mode' => [
            'default' => 'table',
            'available' => [
                'list',
                'table'
            ]
        ],
        'showImage' => [
            'default' => 'yes',
            'available' => [
                'yes',
                'no'
            ]
        ],
        'showConfigurableImage' => [
            'default' => 'no',
            'available' => [
                'yes',
                'no'
            ]
        ],
        'showPrice' => [
            'default' => 'yes',
            'available' => [
                'yes',
                'no'
            ]
        ],
        'priceFormat' => [
            'default' => 'excludeTax',
            'available' => [
                'excludeTax',
                'includeTax'
            ]
        ],
        'showDescription' => [
            'default' => 'yes',
            'available' => [
                'yes',
                'no'
            ]
        ],
        'optionList' => [
            'default' => 'yes',
            'available' => [
                'yes',
                'no'
            ]
        ],
    ];

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    public function __construct(
        Context $context,
        Image $imageHelper,
        Data $dataHelper,
        Configuration $productConfig,
        PriceCurrencyInterface $priceCurrency,
        UrlManager $urlManager,
        ProductRepositoryInterface $productRepository,
        Configurable $configurableType,
        Type $bundleType,
        Grouped $groupedType,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        $this->_imageHelper = $imageHelper;
        $this->_dataHelper = $dataHelper;
        $this->_productConfig = $productConfig;
        $this->_priceCurrency = $priceCurrency;
        $this->_urlManager = $urlManager;
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
        $this->bundleType = $bundleType;
        $this->groupedType = $groupedType;
        $this->configProvider = $configProvider;

        return parent::__construct($context, $data);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function _getLayoutParam($key)
    {
        $func = 'get' . $key;

        return in_array($this->$func(), $this->_params[$key]['available']) ? $this->$func(
        ) : $this->_params[$key]['default'];
    }

    /**
     * @param string $mode
     *
     * @return mixed
     */
    public function setMode($mode)
    {
        $this->setTemplate('email/' . $mode . '.phtml');

        return parent::setMode($mode);
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->_getLayoutParam('mode');
    }

    /**
     * @return bool
     */
    public function showImage()
    {
        return $this->_getLayoutParam('showImage') == 'yes';
    }

    /**
     * @return bool
     */
    public function showConfigurableImage()
    {
        return $this->_getLayoutParam('showConfigurableImage') == 'yes';
    }

    /**
     * @return bool
     */
    public function showPrice()
    {
        return $this->_getLayoutParam('showPrice') == 'yes';
    }

    /**
     * @return bool
     */
    public function showDescription()
    {
        return $this->_getLayoutParam('showDescription') == 'yes';
    }

    /**
     * @return bool
     */
    public function showPriceIncTax()
    {
        return $this->_getLayoutParam('priceFormat') == 'includeTax';
    }

    /**
     * @return bool
     */
    public function showOptionList()
    {
        return $this->_getLayoutParam('optionList') == 'yes';
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     */
    public function initProductImageHelper($product, $imageId)
    {
        if ($this->getQuote()) {
            foreach ($this->getQuote()->getAllItems() as $item) {
                if ($item->getParentItemId() && $item->getParentItemId() == $product->getId()) {
                    $product = $item;
                    break;
                }
            }
        }

        $this->_imageHelper->init($product, $imageId);
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     */
    public function getProductImageHelper()
    {
        return $this->_imageHelper;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Quote\Model\Quote $item
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct($item)
    {
        $product = null;

        if ($item instanceof \Magento\Catalog\Api\Data\ProductInterface) {
            $product = $this->productRepository->getById($item->getId());
        } elseif ($item->getProduct()) {
            $product = $this->productRepository->getById($item->getProductId());
        } elseif ($item->getQuote()) {
            if ($this->showConfigurableImage() && $item->getProductType() == 'configurable') {
                $product = $this->productRepository->get($item->getSku(), false, $item->getQuote()->getStoreId());
            } else {
                $product = $this->productRepository->getById(
                    $item->getProductId(),
                    false,
                    $item->getQuote()->getStoreId()
                );
            }

        } else {
            $product = $item->getProduct();
        }

        return $product;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function getProductOptions($item, $product)
    {
        $optionsData = $item->getProduct()
            ? $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct())
            : [];

        if (isset($optionsData['attributes_info'])) {
            $optionsData = $optionsData['attributes_info'];
        } else {
            $optionsData = [];

            foreach ($product->getCustomOptions() as $key => $option) {
                if ($option->getData('label')) {
                    $optionsData[] = $option->getData();
                }
            }
        }

        return $optionsData;
    }

    public function getFormatedOptionValue($optionValue)
    {
        /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
        $helper = $this->_productConfig;
        $params = [
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
        ];

        return $helper->getFormattedOptionValue($optionValue, $params);
    }

    /**
     * @param float $price
     *
     * @return float
     */
    public function formatPrice($price)
    {
        return $this->_priceCurrency->format(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        );
    }

    /**
     * @param \Magento\Catalog\Model\Product $item
     *
     * @return float
     */
    public function getPrice($item)
    {
        $price = null;
        if ($this->showPriceIncTax()) {
            $price = $this->_dataHelper->getTaxPrice($item, $item->getFinalPrice(), true);
        } else {
            $price = $item->getPrice();

            if (!$price) {
                $price = $item->getFinalPrice();
            }
        }

        return $this->formatPrice($price);
    }

    /**
     * Initialize url for product
     */
    protected function _initUrlManager()
    {
        if (!$this->_urlManager->getRule()) {
            $this->_urlManager->init($this->getRule(), $this->getHistory());
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $item
     *
     * @return string
     */
    public function getProductUrl($item)
    {
        $this->_initUrlManager();

        if ($item->getRedirectUrl()) {
            return $item->getRedirectUrl();
        }

        $option = $item->getOptionByCode('product_type');

        if ($option) {
            $item = $option->getProduct();
        }

        if ($item->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
            $parentProductIds = $this->getParentIdsByChild($item->getId());

            if (!empty($parentProductIds[self::FIRST_PARENT_PRODUCT])) {
                $item = $this->productRepository->getById($parentProductIds[self::FIRST_PARENT_PRODUCT]);
            }
        }

        // return $this->_urlManager->get($item->getUrlModel()->getUrl($item));
        $val = str_replace("en-us/","en-us/products/",$item->getUrlModel()->getUrl($item));
        return $this->_urlManager->get($val);
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $items = [];

        if ($this->getQuote()) {
            $childBlock = $this->getChildBlock('amasty.acart.items.data');
            $childBlock->setQuote($this->getQuote());
            $items = $childBlock->getItems();
        }

        return $items;
    }

    /**
     * @param int $itemId
     *
     * @return array
     */
    private function getParentIdsByChild($itemId)
    {
        $parentProductIds = [];

        if ($this->configurableType->getParentIdsByChild($itemId)) {
            $parentProductIds = $this->configurableType->getParentIdsByChild($itemId);
        } elseif ($this->bundleType->getParentIdsByChild($itemId)) {
            $parentProductIds = $this->bundleType->getParentIdsByChild($itemId);
        } elseif ($this->groupedType->getParentIdsByChild($itemId)) {
            $parentProductIds = $this->groupedType->getParentIdsByChild($itemId);
        }

        return $parentProductIds;
    }

    /**
     * @param string $imgUrl
     *
     * @return string
     */
    public function prepareProductImageUrl($imgUrl)
    {
        if ($this->configProvider->getRemovePubFromImgUrl() && strpos($imgUrl, '/pub/media/') !== false) {
            $imgUrl = str_replace('/pub/', '/', $imgUrl);
        }

        return $imgUrl;
    }
}
