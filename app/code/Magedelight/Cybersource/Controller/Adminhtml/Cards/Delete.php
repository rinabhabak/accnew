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
namespace Magedelight\Cybersource\Controller\Adminhtml\Cards;

class Delete extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    protected $_cardModel;
    protected $_customerFactory;
    protected $_dataObject;
    protected $resultJsonFactory;
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
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Cybersource\Model\Cards $cardModel,
        \Magento\Framework\DataObject $dataObject,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magedelight\Cybersource\Model\Api\Soap $soapModel,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magedelight\Cybersource\Helper\Data $errorHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_cardModel = $cardModel;
        $this->_dataObject = $dataObject;
        $this->_soapModel = $soapModel;
        $this->_customerFactory = $customerFactory;
        $this->_errorHelper = $errorHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $customerId = $this->getRequest()->getParam('id');
        $customer = $this->_customerFactory->create()->load($customerId);
        $append = '';
        $deleteCardId = $this->getRequest()->getParam('customercardid');
        if (!empty($deleteCardId)) {
            $cardModel = $this->_cardModel->load($deleteCardId);
            $subscriptionId = $cardModel->getData('subscription_id');
            if ($subscriptionId && $customerId) {
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
                        $append .= '<div id="messages"><div class="messages"><div class="message message-success success"><div data-ui-id="messages-message-success">'.__('Card deleted successfully.').'</div></div></div></div>';
                        $result = ['error' => false, 'message' => $append];
                    } else {
                        $errorMessage = $this->_errorMessage[$code];
                        $append .= '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.$code.' : '.$this->_errorHelper->getErrorDescription($code).'</div></div></div></div>';
                        $result = ['error' => true, 'message' => $append];
                    }
                } catch (\Exception $e) {
                    $append .= '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.$e->getMessage().'</div></div></div></div>';
                    $result = ['error' => true, 'message' => $append];
                }
            } else {
                $append .= '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.'Card does not exists.'.'</div></div></div></div>';
                $result = ['error' => true, 'message' => $append];
            }
        } else {
            $append .= '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.'Unable to find card to delete.'.'</div></div></div></div>';
            $result = ['error' => true, 'message' => $append];
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);

        return $resultJson;
    }
    protected function _isAllowed()
    {
        return true;
    }
}
