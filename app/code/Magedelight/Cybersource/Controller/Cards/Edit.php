<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Cybersource
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Cybersource\Controller\Cards;

use Magento\Framework\App\RequestInterface;

class Edit extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    protected $_customerSession;

    protected $_cardModel;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magedelight\Cybersource\Model\Cards $cardModel,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_cardModel = $cardModel;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    protected function _getSession()
    {
        return $this->_customerSession;
    }

    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }

        return parent::dispatch($request);
    }
    public function execute()
    {
        try {
            #DebugBreak();
            $resultPage = $this->resultPageFactory->create();
            $resultRedirect = $this->resultRedirectFactory->create();

            $editId = $this->getRequest()->getPostValue('card_id');
            $customerId = $this->_customerSession->getCustomerId();

            if ($editId && $customerId) {
                $cardModel = $this->_cardModel->load($editId);
                if($cardModel->getCustomerId() != $customerId){
                    throw new \Exception('Customer Card not found.');
                }
                $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
                if ($navigationBlock) {
                    $navigationBlock->setActive('magedelight_cybersource/cards/listing/');
                }
                return $resultPage;
            } else {
                throw new \Exception('Card information missing.');
            }            
        }
        catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage()));
            return $resultRedirect->setPath('*/*/listing');
        }
        return $resultPage;
    }
}
