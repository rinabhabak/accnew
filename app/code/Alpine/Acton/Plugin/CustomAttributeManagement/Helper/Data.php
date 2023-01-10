<?php
/* Alpine_Acton
 *
 * @category    Alpine
 * @package     Alpine_Acton
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Danila Vasenin <danila.vasenin@alpineinc.com>
 */

namespace Alpine\Acton\Plugin\CustomAttributeManagement\Helper;

/**
 * Alpine\Acton\CustomAttributeManagement\Helper\Data
 *
 * @category    Alpine
 * @package     Alpine_Acton
 */
class Data
{
    /**
     * afterGetAttributeInputTypes
     *
     * @param $subject
     * @param $inputTypes
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetAttributeInputTypes($subject, $proceed, $inputType = null)
    {
        $result = $proceed();
        
        if ($inputType == 'checkbox') {
            return [
                'label' => __('Checkbox Yes/No'),
                'manage_options' => false,
                'validate_types' => [],
                'validate_filters' => [],
                'filter_types' => [],
                'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'backend_type' => 'int',
                'default_value' => 'yesno',
            ];
        }
      
        return $result;
    }
}
