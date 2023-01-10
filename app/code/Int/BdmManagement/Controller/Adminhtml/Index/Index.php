<?php

namespace Int\BdmManagement\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
	
    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Int_BdmManagement::bdmmanagement_manage');
    }

    /**
     * BdmManagement List action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Int_BdmManagement::bdmmanagement_manage'
        )->addBreadcrumb(
            __('BDM Management'),
            __('BDM Management')
        )->addBreadcrumb(
            __('Manage BDM'),
            __('Manage BDM')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('BDM Management'));
        return $resultPage;
    }
}
