<?php

namespace AddThis\FloatingShareBar\Model\Source;

class Counts implements \Magento\Framework\Option\ArrayInterface
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
                'value' => 'one',
                'label' => __('Total')
            ],
            [
                'value' => 'each',
                'label' => __('Individual')
            ],
            [
                'value' => 'both',
                'label' => __('Individual & Total')
            ],
            [
                'value' => 'none',
                'label' => __('None')
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
        return ['one' => __('Total'),
                'each' => __('Individual'),
                'both' => __('Individual & Total'),
                'none' => __('None')
        ];
    }
}
