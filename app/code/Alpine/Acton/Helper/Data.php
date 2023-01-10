<?php
/**
 * Alpine_Acton
 *
 * @category    Alpine
 * @package     Alpine_Acton
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */

namespace Alpine\Acton\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Alpine\Acton\Helper\Data
 *
 * @category    Alpine
 * @package     Alpine_Acton
 */
class Data extends AbstractHelper
{
    /**
     * Constant for attributes code
     *
     * @var string
     */
    const XML_CONFIG_PATH_ACTON_FORMS_MAPPING = 'alpine_acton/forms/mapping';
    
    /**
     * Get forms mapping
     *
     * @return string
     */
    public function getFormsMapping()
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_ACTON_FORMS_MAPPING);
    }
}
