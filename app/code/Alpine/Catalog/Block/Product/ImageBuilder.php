<?php
/**
 * Alpine overridden block
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Evgeniy Derevyanko <evgeniy.derevyanko@alpineinc.com>
 */

namespace Alpine\Catalog\Block\Product;

use Magento\Catalog\Helper\ImageFactory as HelperFactory;
use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Catalog\Model\Product\Image\NotLoadInfoImageException;
use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Alpine overridden block class
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 */
class ImageBuilder extends \Magento\Catalog\Block\Product\ImageBuilder
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @param HelperFactory $helperFactory
     * @param ImageFactory $imageFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        HelperFactory $helperFactory,
        ImageFactory $imageFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($helperFactory, $imageFactory);
    }
    
    /**
     * @inheritdoc
     */
    public function create(
        \Magento\Catalog\Model\Product $product = null,
        string $imageId = null,
        array $attributes = null
    ) {
        $configValue = $this->scopeConfig->getValue('checkout/cart/configurable_product_image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $product     = $product ?? $this->product;

        $simpleOption = $product->getCustomOption('simple_product');
        if ($simpleOption !== null) {
            $optionProduct = $simpleOption->getProduct();
            if ($configValue !== 'parent') {
                $product = $optionProduct;
            }
        }
        
        return parent::create($product, $imageId, $attributes);
    }
}
