<?php
/**
 * Alpine_Catalog
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\Catalog\Block\Product\Tabs;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface as JsonEncoder;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

/**
 * Alpine\Catalog\Block\Product\Tabs\Info
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 */
class Info extends View
{
    /**
     * Json serializer
     *
     * @var Json
     */
    protected $serializer;
    
    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Context $context
     * @param EncoderInterface $urlEncoder
     * @param JsonEncoder $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param Json $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $urlEncoder,
        JsonEncoder $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        Json $serializer,
        array $data = []
    ) {
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
            $priceCurrency,
            $data
        );
        
        $this->serializer = $serializer;
        $this->logger = $context->getLogger();
    }

    /**
     * Get tab info
     *
     * @return array
     */
    public function getInfo()
    {
        $result = [];
        
        $attributeCode = $this->getAttributeCode();
        
        $product = $this->getProduct();
        $attributeValue = $product->getData($attributeCode);
        
        if ($attributeValue && $attributeValue != $this->escapeHtml($attributeValue)
            && $attributeValue != strip_tags($attributeValue)
        ) {
            $result = [
                'html',
                $attributeValue
            ];
        } else {
            try {
                $result = [
                    'json',
                    $this->serializer->unserialize($attributeValue)
                ];
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
        
        return $result;
    }
    
    /**
     * Get image url
     *
     * @param string $path
     * @return string
     */
    public function getImageUrl($path)
    {
        $result = '';
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $result = $path;
        } else {
            $mediaUrl = $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
            $result = $mediaUrl . ltrim($path, DIRECTORY_SEPARATOR);
        }
        
        return $result;
    }
}
