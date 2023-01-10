<?php
/**
 * Alpine_Catalog
 *
 * @category    Alpine
 * @package     Alpine_Accuride
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Denis Furman <denis.furman@alpineinc.com>
 */

namespace Alpine\Catalog\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\ProductTypes\ConfigInterface as ConfigInterfaceProduct;
use Magento\Customer\Model\Session as SessionCustomer;
use Magento\Framework\Json\EncoderInterface as EncoderInterfaceJson;
use Magento\Framework\Locale\FormatInterface as FormatInterfaceLocale;
use Magento\Framework\Pricing\PriceCurrencyInterface as PriceCurrencyInterfacePricing;
use Magento\Framework\Stdlib\StringUtils as StringUtilsStd;
use Magento\Framework\Url\EncoderInterface;

class View extends \Magento\Catalog\Block\Product\View
{

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @param Context $context
     * @param EncoderInterface $urlEncoder
     * @param EncoderInterfaceJson $jsonEncoder
     * @param StringUtilsStd $string
     * @param ProductHelper $productHelper
     * @param ConfigInterfaceProduct $productTypeConfig
     * @param FormatInterfaceLocale $localeFormat
     * @param SessionCustomer $customerSession
     * @param ProductRepositoryInterface|PriceCurrencyInterfacePricing $productRepository
     * @param PriceCurrencyInterfacePricing $priceCurrency
     * @param array $data
     * @codingStandardsIgnoreStart
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        EncoderInterface $urlEncoder,
        EncoderInterfaceJson $jsonEncoder,
        StringUtilsStd $string,
        ProductHelper $productHelper,
        ConfigInterfaceProduct $productTypeConfig,
        FormatInterfaceLocale $localeFormat,
        SessionCustomer $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterfacePricing $priceCurrency,
        array $data = []
    )
    {
        $this->_productHelper = $productHelper;
        $this->urlEncoder = $urlEncoder;
        $this->_jsonEncoder = $jsonEncoder;
        $this->productTypeConfig = $productTypeConfig;
        $this->string = $string;
        $this->_localeFormat = $localeFormat;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency
        );
    }

    /**
     * Gets max sales quantity
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int|null
     */
    public function getMaxQty($product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $maxSaleQty = $stockItem->getMaxSaleQty();
        return $maxSaleQty > 0 ? $maxSaleQty : null;
    }
}
