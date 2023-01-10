<?php

namespace AddThis\FloatingShareBar\Model\Source;

class DesktopPosition implements \Magento\Framework\Option\ArrayInterface
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
                'value' => 'left',
                'label' => __('Left')
            ],
            [
                'value' => 'right',
                'label' => __('Right')
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
        return ['left' => __('Left'),
                'right' => __('Right')
        ];
    }
}
