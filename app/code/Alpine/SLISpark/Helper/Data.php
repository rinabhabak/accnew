<?php
/**
 * Alpine_SLISpark
 *
 * @category    Alpine
 * @package     Alpine_SLISpark
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Danila Vasenin <danila.vasenin@alpineinc.com>
 */

namespace Alpine\SLISpark\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Alpine\SLISpark\Helper\Data
 *
 * @category    Alpine
 * @package     Alpine_SLISpark
 */
class Data extends AbstractHelper
{
    /**
     * Constant for attributes code
     *
     * @var string
     */
    const XML_CONFIG_PATH_SLI_JS = 'alpine_sli/general/js';
    
    /**
     * Get forms mapping
     *
     * @return string
     */
    public function getJsConfig()
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_SLI_JS);
    }
}
