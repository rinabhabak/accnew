<?php
/**
 * Show the logos under product tabs
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Catalog\Block\Product;

use Magento\Catalog\Block\Product\View;

class Logos extends View
{
    /**
     * Get Attribute value
     *
     * @param $attributeCode
     * @return int|null
     */
    public function getAttributeValue($attributeCode)
    {
        if ($product = $this->getProduct()) {
            /* @var $customAttribute \Magento\Framework\Api\AttributeInterface */
            $customAttribute = $product->getCustomAttribute($attributeCode);
            if ($customAttribute) {
                return $customAttribute->getValue();
            }
        }
        return null;
    }
}