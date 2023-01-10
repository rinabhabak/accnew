<?php
/**
 * Short description / title of module
 *
 * @category    Alpine
 * @package     Alpine_PackageName
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Aleksandr Mikhailov (aleksandr.mikhailov@alpineinc.com)
 */

namespace Alpine\DisableNewsletter\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * Path to "Newsletter enabled" config field
     *
     * @var string
     */
    const XML_PATH_NEWSLETTER_ENABLED = 'newsletter/subscription/newsletter_enabled';

    /**
     * Is Newsletter enabled
     *
     * @return bool
     */
    public function isNewsletterEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NEWSLETTER_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
}