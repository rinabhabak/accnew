<?php

namespace AddThis\FloatingShareBar\Model\Source;

class MobilePosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'top',
                'label' => __('Top')
            ],
            [
                'value' => 'bottom',
                'label' => __('Bottom')
            ],
            [
                'value' => 'hide',
                'label' => __('Hide')
            ],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['top' => __('Top'),
                'bottom' => __('Bottom'),
                'hide' => __('Hide')
        ];
    }
}
