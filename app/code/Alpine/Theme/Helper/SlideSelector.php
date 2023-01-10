<?php
/**
 * Slide Selector Helper
 *
 * @category    Alpine
 * @package     Alpine_Theme
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\Theme\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Slide Selector
 *
 * @category    Alpine
 * @package     Alpine_Theme
 */
class SlideSelector extends AbstractHelper
{
    /**
     * Entity-Attribute-Value Config
     *
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * Constructor of class SlideSelector
     *
     * @param Context $context
     * @param EavConfig $eavConfig
     */
    public function __construct(Context $context, EavConfig $eavConfig)
    {
        parent::__construct($context);
        $this->eavConfig = $eavConfig;
    }

    /**
     * Get Values of Options
     *
     * @param AbstractAttribute $productAttribute
     * @return array
     */
    public function getOptionsValues(AbstractAttribute $productAttribute)
    {
        $result = [];
        foreach ($productAttribute->getSource()->getAllOptions() as $option) {
            if ($option['value']) {
                $result [$option['label']] = $option['value'];
            }
        }
        return $result;
    }
    
    /**
     * Get attribute by code
     *
     * @param string $code
     * @return AbstractAttribute|boolean
     */
    public function getAttributeByCode($code)
    {
        try {
            $result = $this->eavConfig->getAttribute(Product::ENTITY, $code);
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
            $result = false;
        }
        
        return $result;
    }
    
    /**
     * Get selector label
     *
     * @param AbstractAttribute $attribute
     * @return string
     */
    public function getSelectorLabel(AbstractAttribute $attribute)
    {
        return strtoupper($attribute->getFrontendLabel());
    }
}
