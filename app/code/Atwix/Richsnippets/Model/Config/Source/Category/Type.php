<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Model\Config\Source\Category;

use Atwix\Richsnippets\Helper\Data as DataHelper;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Please, select type of display')],
            ['value' => DataHelper::SNIPPET_TYPE_SIDEBAR, 'label' => __('Visible: Sidebar (recommend)')],
            ['value' => DataHelper::SNIPPET_TYPE_VISIBLE, 'label' => __('Visible: Block (content)')],
            ['value' => DataHelper::SNIPPET_TYPE_FOOTER,  'label' => __('Visible: Block (footer)')],
            ['value' => DataHelper::SNIPPET_TYPE_JSON,    'label' => __('Hidden: JSON')],
        ];
    }
}