<?php
/**
 * Copyright (c) 2015 S.L.I. Systems, Inc. (www.sli-systems.com) - All Rights Reserved
 * This file is part of Learning Search Connect.
 * Learning Search Connect is distributed under a limited and restricted
 * license – please visit www.sli-systems.com/LSC for full license details.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE. TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO
 * EVENT WILL SLI BE LIABLE TO YOU OR ANY OTHER PARTY FOR ANY GENERAL, DIRECT,
 * INDIRECT, SPECIAL, INCIDENTAL OR CONSEQUENTIAL LOSS OR DAMAGES OF ANY
 * CHARACTER ARISING OUT OF THE USE OF THE CODE AND/OR THE LICENSE INCLUDING
 * BUT NOT LIMITED TO PERSONAL INJURY, LOSS OF DATA, LOSS OF PROFITS, LOSS OF
 * ASSIGNMENTS, DATA OR OUTPUT FROM THE SERVICE BEING RENDERED INACCURATE,
 * FAILURE OF CODE, SERVER DOWN TIME, DAMAGES FOR LOSS OF GOODWILL, BUSINESS
 * INTERRUPTION, COMPUTER FAILURE OR MALFUNCTION, OR ANY AND ALL OTHER DAMAGES
 * OR LOSSES OF WHATEVER NATURE, EVEN IF SLI HAS BEEN INFORMED OF THE
 * POSSIBILITY OF SUCH DAMAGES.
 */

namespace SLI\Cors\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Module\ResourceInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\Context;

class CorsHelper extends AbstractHelper
{
    const XML_PATH_CORS_ENABLED = 'sli_search_cors/general/enabled';
    const XML_PATH_SUBDOMAIN = 'sli_search_cors/subdomain/subdomain';

    /**
     * The store manager
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * A set of subdomains that are allowed via CORS.
     * @var array
     */
    protected $subdomains;

    /**
     * Access Module info.
     * @var ResourceInterface
     */
    protected $moduleResource;

    /**
     * CorsHelper constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ResourceInterface $moduleResource
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ResourceInterface $moduleResource
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->moduleResource = $moduleResource;
        $this->loadSubdomains();
    }

    /**
     * Checks if CORS module config is enabled.
     * @return boolean true for enabled, false for disabled.
     */
    public function isCorsEnabled()
    {
        return 1 == (int)$this->scopeConfig->getValue(
                self::XML_PATH_CORS_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $this->getStore());
    }

    /**
     * Checks an a domain string is allowed
     * @param string $domain for the domain
     * @return bool true if valid.
     */
    public function checkSubdomain($domain)
    {
        return array_key_exists(trim($domain), $this->subdomains);
    }

    /**
     * Loads the subdomains from the saved config into a set.
     * @return array the set of subdomains
     */
    private function loadSubdomains()
    {
        $subdomainsString = $this->getSubdomains();
        $domains = explode("\n", $subdomainsString);
        $this->subdomains = [];
        foreach ($domains as $domain) {
            $cleanedDomain = trim($domain);
            if (!empty($cleanedDomain)) {
                $this->subdomains[$cleanedDomain] = 1;
            }
        }
        return $this->subdomains;
    }

    /**
     * Returns the subdomains from the magento storage.
     * @return mixed the string of subdomains.
     */
    private function getSubdomains()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUBDOMAIN, ScopeInterface::SCOPE_STORES, $this->getStore());
    }

    /**
     * Gets the store id.
     * @return int
     */
    protected final function getStore()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get current version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->moduleResource->getDbVersion('SLI_Cors');
    }
}