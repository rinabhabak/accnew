<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Model\Config\Source\Rating;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'reviews', 'label' => __('Reviews')],
            ['value' => 'votes', 'label' => __('Votes')]
        ];
    }
}