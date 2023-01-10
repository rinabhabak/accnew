<?php
/**
 * Alpine Ga Helper
 *
 * @category    Alpine
 * @package     Alpine_Ga
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Alex Didenko <alex.didenko@alpineinc.com>
 */

namespace Alpine\Ga\Helper;

use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Alpine Ga Helper
 *
 * @category    Alpine
 * @package     Alpine_Ga
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Magento CMS page factory
     *
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * CMS Page Model
     *
     * @var Page
     */
    protected $page;

    /**
     * Configuration interface
     *
     * @var ScopeConfigInterface
     */
    protected $scopeInterface;

    /**
     * Data constructor
     *
     * @param PageFactory $pageFactory
     * @param Page $page
     * @param ScopeConfigInterface $scopeInterface
     */
    public function __construct(
        PageFactory $pageFactory,
        Page $page,
        ScopeConfigInterface $scopeInterface
    ) {
        $this->pageFactory = $pageFactory;
        $this->_page = $page;
        $this->_scopeConfig = $scopeInterface;
    }

    /**
     * Get title of CMS page
     *
     * @return string
     */
    public function getCMSPageTitle() {
        $pageId = '';

        if ($this->_page->getId()) {
            $pageId = $this->_page->getId();
        }

        $pageTitle = $this->pageFactory->create()->load($pageId)->getTitle();

        return $pageTitle;
    }

    /**
     * Get Google API key
     *
     * @return string
     */
    public function getGoogleApiKey() {
        $googleKey = $this->_scopeConfig->getValue('google/analytics/account', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $googleKey;
    }
}
