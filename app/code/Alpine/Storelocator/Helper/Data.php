<?php
/**
 * Alpine Storelocator helper
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Storelocator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Alpine Storelocator helper
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 */
class Data extends AbstractHelper
{
    /**
     * Data constructor
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Get config value
     *
     * @param string $path
     * @return string
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
}