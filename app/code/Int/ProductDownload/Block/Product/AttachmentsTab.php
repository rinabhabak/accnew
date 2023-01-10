<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Int\ProductDownload\Block\Product;

use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\ConfigProvider;
use Amasty\ProductAttachment\Model\File\FileScope\FileScopeDataProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class AttachmentsTab extends \Amasty\ProductAttachment\Block\Product\AttachmentsTab
{
    protected $_template = 'Amasty_ProductAttachment::attachments.phtml';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var FileScopeDataProvider
     */
    private $fileScopeDataProvider;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
   protected $scopeConfig;

    public function __construct(
        ConfigProvider $configProvider,
        FileScopeDataProvider $fileScopeDataProvider,
        Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Registry $registry,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($configProvider, $fileScopeDataProvider, $customerSession, $registry, $context, $data);
        $this->configProvider = $configProvider;
        $this->registry = $registry;
        $this->fileScopeDataProvider = $fileScopeDataProvider;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->setData('sort_order', $this->configProvider->getBlockSortOrder());
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        if (!$this->configProvider->isEnabled() || !$this->configProvider->isBlockEnabled()) {
            return '';
        }
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $_customerGroups = explode(',', $this->getBlockCustomerGroups());
        $logger->info(print_r($_customerGroups,1));
        $logger->info($this->customerSession->getCustomerGroupId());
        // $logger->info(!in_array($this->customerSession->getCustomerGroupId(),explode(',', $this->getBlockCustomerGroups())));

        if ($this->getBlockCustomerGroups() !== null) {
            if (!in_array($this->customerSession->getCustomerGroupId(),$_customerGroups)) {
                $logger->info('here 1');
                return '';
            }
        }

        $this->setTitle($this->configProvider->getBlockTitle());

        return parent::toHtml();
    }

    /**
     * @return bool
     */
    public function getBlockTitle()
    {
        return false;
    }

    /**
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface[]|bool
     */
    public function getAttachments()
    {
        if ($product = $this->registry->registry('current_product')) {
            return $this->fileScopeDataProvider->execute(
                [
                    RegistryConstants::PRODUCT => $product->getId(),
                    RegistryConstants::STORE => $this->_storeManager->getStore()->getId(),
                    RegistryConstants::EXTRA_URL_PARAMS => [
                        'product' => (int)$product->getId()
                    ]
                ],
                'frontendProduct'
            );
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isShowIcon()
    {
        return $this->configProvider->isShowIcon();
    }

    /**
     * @return bool
     */
    public function isShowFilesize()
    {
        return $this->configProvider->isShowFilesize();
    }

    /**
     * @return string
     */
    public function getWidgetType()
    {
        return 'tab';
    }

    public function getBlockCustomerGroups() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('amfile/product_tab/customer_group_updated', $storeScope);
    }
}
