<?php
/**
 * Alpine_CustomerCompany
 *
 * @category    Alpine
 * @package     Alpine_CustomerCompany
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\CustomerCompany\Block;

use Magento\CustomerCustomAttributes\Block\Form as BaseForm;
use Magento\Eav\Model\Attribute;

/**
 * Alpine\CustomerCompany\Block\Form
 *
 * @category    Alpine
 * @package     Alpine_CustomerCompany
 */
class Form extends BaseForm
{
    /**
     * Attributes codes
     *
     * @var array
     */
    protected $attributes = [
        'customer_category',
        'pick_your_industry',
        'other_industry'
    ];
    
    /**
     * Return array of user defined attributes
     *
     * @return array
     */
    public function getUserDefinedAttributes()
    {
        $attributes = [];
        foreach ($this->getForm()->getUserAttributes() as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($attribute->getIsVisible() && in_array($code, $this->attributes)) {
                $attributes[$code] = $attribute;
            }
        }
        
        return $attributes;
    }
    
    /**
     * Get attribute options
     *
     * @param Attribute $attribute
     * @return array
     */
    public function getAttributeOptions(Attribute $attribute)
    {
        return $attribute->getSource()->getAllOptions();
    }
}
