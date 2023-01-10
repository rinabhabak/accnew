<?php
/**
 * Page helper
 *
 * @category    Alpine
 * @package     Alpine_SLIFeed
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Evgeniy Derevyanko (evgeniy.derevyanko@alpineinc.com)
 */

namespace Alpine\SLIFeed\Helper;

/**
 * CMS Page Helper
 * 
 * @category    Alpine
 * @package     Alpine_SLIFeed
 */
class Page extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->pageFactory = $pageFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Retrieve page direct URL
     *
     * @param string $pageId
     * @return string|null
     */
    public function getPageUrl($pageId = null)
    {
        /* @var \Magento\Cms\Model\Page $page */
        $result = null;
        if ($pageId) {
            $page = $this->pageFactory->create();
            $page->setStoreId($this->storeManager->getStore()->getId());
            if ($page->load($pageId)) {
                $result = $this->_urlBuilder->getUrl(null, ['_direct' => $page->getIdentifier()]);
            }
        }

        return $result;
    }
}
