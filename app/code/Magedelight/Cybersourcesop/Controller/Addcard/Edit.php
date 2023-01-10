<?php
/**
* Magedelight
* Copyright (C) 2017 Magedelight <info@magedelight.com>
*
* NOTICE OF LICENSE
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
*
* @category Magedelight
* @package Magedelight_Cybersourcedc
* @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
*/
namespace Magedelight\Cybersourcesop\Controller\Addcard;

use Magento\Framework\App\RequestInterface;

class Edit extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    protected $_customerSession;

    protected $paymentCardSaveTokenFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect 
     */
    protected $resultRedirectFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Vault\Model\PaymentTokenFactory $paymentCardSaveTokenFactory,    
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\Redirect $resultRedirectFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_customerSession = $customerSession;
        $this->paymentCardSaveTokenFactory = $paymentCardSaveTokenFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /*public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }

        return parent::dispatch($request);
    }*/
    public function execute()
    {
        
        try {
            #DebugBreak();
            
            $resultPage = $this->resultPageFactory->create();
            $resultRedirect = $this->resultRedirectFactory->create();
            

            $editId = $this->getRequest()->getPostValue('card_id');
            $hasKey = $this->getRequest()->getPostValue('public_hash');
            $customerId = $this->_customerSession->getCustomerId();
            
           

            if ($editId && $customerId) {
                $cardDetails =  $this->paymentCardSaveTokenFactory->create()->getCollection()->addFieldToFilter('public_hash', array("eq" => $hasKey));
                $cardModel = $cardDetails->getData();
                $customerTempId =  $cardModel[0]['customer_id'];
                
                if($customerTempId != $customerId){
                    throw new \Exception('Customer Card not found.');
                }
                    return $resultPage;
                } else {
                    throw new \Exception('Card information missing.');
                }            
        }
        catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage()));
             return $this->redirectByUrl('vault/cards/listaction/');
        }
        return $resultPage;
    }
    public function redirectByUrl($path)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectLink = $this->getReirectUrlByAdminUrl().$path; 
        $resultRedirect->setUrl($redirectLink);
        return $resultRedirect;
    }
    public function getReirectUrlByAdminUrl() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;   
        return $this->scopeConfig->getValue(self::XML_PATH_ADMIN_URL, $storeScope);
    }
}
