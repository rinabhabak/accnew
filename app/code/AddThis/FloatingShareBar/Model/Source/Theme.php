<?php

namespace AddThis\FloatingShareBar\Model\Source;

class Theme implements \Magento\Framework\Option\ArrayInterface
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
                'value' => 'transparent',
                'label' => __('Transparent')
            ],
            [
                'value' => 'light',
                'label' => __('Light')
            ],
            [
                'value' => 'dark',
                'label' => __('Dark')
            ],
            [
                'value' => 'grey',
                'label' => __('Grey')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['transparent' => __('Transparent'),
                'light' => __('Light'),
                'dark' => __('Dark'),
                'grey' => __('Grey')
        ];
    }
}
