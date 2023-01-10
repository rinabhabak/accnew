<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Int\ProductDownload\Model\SourceOptions;

use Magento\Framework\Option\ArrayInterface;

class CustomerGroup implements ArrayInterface
{

    /**
     * @var \Magento\Customer\Model\Customer\Attribute\Source\Group
     */
    protected $groupSource;

    /**
     * Customer Group
     *
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $_customerGroup;

    public function __construct(\Magento\Customer\Model\Customer\Attribute\Source\Group $groupSource,\Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup)
    {
        $this->groupSource = $groupSource;
        $this->_customerGroup = $customerGroup;     
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $stepId => $label) {
            $optionArray[] = ['value' => $stepId, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = $this->groupSource->getAllOptions();
        $result[0] = __('NOT LOGGED IN');
        $result[1] = __('General');
        
        /**
         * B2B Fix
         */
        if (!empty($options[0]) && is_array($options[0]['value'])) {
            $options = $options[0]['value'];
        }
        if (!empty($options[1]) && is_array($options[1]['value'])) {
            $options = $options[1]['value'];
        }

        foreach ($options as $option) {
            $result[$option['value']] = $option['label'];
        }

        return $result;
    }
}
