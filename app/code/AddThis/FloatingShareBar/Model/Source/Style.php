<?php

namespace AddThis\FloatingShareBar\Model\Source;

class Style implements \Magento\Framework\Option\ArrayInterface
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
                'value' => 'modern',
                'label' => __('Modern')
            ],
            [
                'value' => 'bordered',
                'label' => __('Modern (Bordered)')
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
        return ['modern' => __('Modern'),
                'bordered' => __('Modern (Bordered)'),
        ];
    }
}
