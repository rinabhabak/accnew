<?php
/**
 * Alpine_ConfigurableChildVisibility
 *
 * @category    Alpine
 * @package     Alpine_Accuride
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Derevyanko Evgeniy (evgeniy.derevyanko@alpineinc.com)
 */

namespace Alpine\ConfigurableChildVisibility\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;

/**
 * Class ConfigurableAttributeData
 */
class ConfigurableAttributeData extends \Magento\ConfigurableProduct\Model\ConfigurableAttributeData
{
    /**
     * @param Attribute $attribute
     * @param array $config
     * @return array
     */
    protected function getAttributeOptionsData($attribute, $config)
    {
        $attributeOptionsData = [];
        foreach ($attribute->getOptions() as $attributeOption) {
            $optionId = $attributeOption['value_index'];
            $attributeOptionsData[] = [
                'id' => $optionId,
                'label' => $attributeOption['label'],
                'products' => isset($config[$attribute->getAttributeId()][$optionId])
                    ? $config[$attribute->getAttributeId()][$optionId]
                    : [],
                'count' => isset($config['count'][$attribute->getAttributeId()][$optionId])
                    ? $config['count'][$attribute->getAttributeId()][$optionId]
                    : []
            ];
        }
        return $attributeOptionsData;
    }
}
