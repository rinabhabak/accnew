<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Model\Config\Source\Category;

use Atwix\Richsnippets\Helper\Data as DataHelper;

class Position implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Please, select type of display')],
            ['value' => DataHelper::SNIPPET_POSITION_BEFORE, 'label' => __('Before Content')],
            ['value' => DataHelper::SNIPPET_POSITION_AFTER, 'label' => __('After Content')],
        ];
    }
}