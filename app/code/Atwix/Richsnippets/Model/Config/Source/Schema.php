<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Model\Config\Source;

use Atwix\Richsnippets\Helper\Data as DataHelper;

class Schema implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Please, select schema type')],
            ['value' => DataHelper::BC_SNIPPET_TYPE_SCHEMA, 'label' => __('Schema.org')],
            ['value' => DataHelper::BC_SNIPPET_TYPE_RDF,    'label' => __('RDF')],
            ['value' => DataHelper::BC_SNIPPET_TYPE_JSON,   'label' => __('JSON')],
        ];
    }
}