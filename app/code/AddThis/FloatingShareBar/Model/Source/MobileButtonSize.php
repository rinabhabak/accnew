<?php

namespace AddThis\FloatingShareBar\Model\Source;

class MobileButtonSize implements \Magento\Framework\Option\ArrayInterface
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
                'value' => 'large',
                'label' => __('Large')
            ],
            [
                'value' => 'medium',
                'label' => __('Medium')
            ],
            [
                'value' => 'small',
                'label' => __('Small')
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
        return ['large' => __('Large'),
                'medium' => __('Medium'),
                'small' => __('Small')
        ];
    }
}
