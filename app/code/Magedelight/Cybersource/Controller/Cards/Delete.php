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

class Delete extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    protected $_customerSession;

    protected $_cardModel;

    protected $_dataObject;

    protected $_soapModel;

    protected $_errorHelper;
    protected $_errorMessage = array(
        '100' => 'Successful transaction',
        '101' => 'Missing required fields',
        '102' => 'Invalid data',
        '110' => 'Partial amount approved',
        '150' => 'General system failure',
        '151' => 'The request was received but there was a server timeout. This error does not
        include timeouts between the client and the server',
        '152' => 'The request was received, but a service did not finish running in time',
        '200' => 'The authorization request was approved by the issuing bank but declined by
        CyberSource because it did not pass the AVS check',
        '201' => 'The issuing bank has questions about the request. You will not receive an
        authorization code programmatically, but you can obtain one verbally by calling
        the processor',
        '202' => 'Expired card',
        '203' => 'General decline of the card. No other information provided by the issuing bank',
        '204' => 'Insufficient funds in the account',
        '205' => 'Stolen or lost card',
        '207' => 'Issuing bank unavailable',
        '208' => 'Inactive card or card not authorized for card-not-present transactions',
        '209' => 'American Express Card Identification Digits (CIDs) did not match',
        '210' => 'The card has reached the credit limit',
        '211' => 'Invalid card verification number',
        '220' => "The processor declined the request based on a general issue with the
        customer's account",
        '221' => "The customer matched an entry on the processor's negative file",
        '222' => "The customer's bank account is frozen",
        '230' => 'The authorization request was approved by the issuing bank but declined by
        CyberSource because it did not pass the CVN check',
        '231' => 'Invalid account number',
        '232' => 'The card type is not accepted by the payment processor',
        '233' => 'General decline by the processor',
        '234' => 'There is a problem with your CyberSource merchant configuration',
        '236' => 'Processor failure',
        '240' => 'The card type sent is invalid or does not correlate with the card number',
        '250' => 'The request was received, but there was a timeout at the payment processor',
    );
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Cybersource\Model\Cards $cardModel,
        \Magento\Framework\DataObject $dataObject,
        \Magedelight\Cybersource\Model\Api\Soap $soapModel,
        \Magedelight\Cybersource\Helper\Data $errorHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->_cardModel = $cardModel;
        $this->_dataObject = $dataObject;
        $this->_soapModel = $soapModel;
        $this->_errorHelper = $errorHelper;
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
        $resultRedirect = $this->resultRedirectFactory->create();
        $deleteCardId = $this->getRequest()->getPostValue('card_id');
        if (!empty($deleteCardId)) {
            $cardModel = $this->_cardModel->load($deleteCardId);
            $subscriptionId = $cardModel->getData('subscription_id');
            $customerId = $this->_customerSession->getCustomerId();
            if ($subscriptionId && $customerId && $cardModel->getCustomerId() == $customerId) {
                $requestObject = $this->_dataObject;
                $requestObject->addData(array(
                    'customer_subscription_id' => $subscriptionId,
                ));
                try {
                    $response = $this->_soapModel->setInputData($requestObject)
                    ->deleteCustomerPaymentProfile();
                    $code = $response->reasonCode;
                    $deleteResultCode = $response->paySubscriptionDeleteReply->reasonCode;

                    if ($code == '100' && $deleteResultCode == '100') {
                        $cardModel->delete();
                        $this->messageManager->addSuccess(__('Card deleted successfully.'));
                    } else {
                        $errorMessage = $this->_errorMessage[$code];
                        $this->messageManager->addError($code.' : '.$this->_errorHelper->getErrorDescription($code));
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());

                    return $resultRedirect->setPath('*/*/listing');
                }
            } else {
                $this->messageManager->addError('Card does not exists.');

                return $resultRedirect->setPath('*/*/listing');
            }

            return $resultRedirect->setPath('*/*/listing');
        } else {
            $this->messageManager->addError('Unable to find card to delete.');

            return $resultRedirect->setPath('*/*/listing');
        }
    }
}
