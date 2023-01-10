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

class Save extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    protected $_customerFactory = null;

    protected $_requestObject;

    protected $_customerSession;
    protected $resultJsonFactory;

    protected $_soapModel;

    protected $_cardModel;
    
    protected $cybersourceHelper;

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
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\DataObject $requestObject,
        \Magedelight\Cybersource\Model\Api\Soap $soapModel,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magedelight\Cybersource\Helper\Data $cybersourceHelper,    
        \Magedelight\Cybersource\Model\Cards $cardModel
    ) {
        $this->_customerFactory = $customerFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_requestObject = $requestObject;
        $this->_soapModel = $soapModel;
        $this->_cardModel = $cardModel;
        $this->cybersourceHelper    = $cybersourceHelper;
        parent::__construct($context);
    }

    public function getCustomer($customerId)
    {
        return $this->_customerFactory->create()->load($customerId);
    }

    public function execute()
    {
        $append = '';
        $websiteId = $this->cybersourceHelper->getWebsiteId();
        $errorMessage = '';
        $params = $this->getRequest()->getParams();
        $customerId = $params['id'];
        if (!$params) {
            $message = '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.__('Please try again.').'</div></div></div></div>';
            $result = ['error' => true, 'message' => $message];
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($result);
            return $resultJson;
        }
        #DebugBreak();
        $customer = $this->getCustomer($customerId);
        try {
            $requestObject = $this->_requestObject;
            $requestObject->addData(array(
                'customer_id' => $customer->getId(),
                'email' => $customer->getEmail(),
            ));
            $requestObject->addData($params['paymentParam']);
            $response = $this->_soapModel
            ->setInputData($requestObject)
            ->createCustomerProfile();
            $code = $response->reasonCode;
            $profileResponsecheck = $response->reasonCode;
            if ($code == '100' && $profileResponsecheck == '100') {
                $subscriptionId = $response->paySubscriptionCreateReply->subscriptionID;
                if (!empty($subscriptionId)) {
                    $model = $this->_cardModel
                    ->setData($params['paymentParam'])
                    ->setCustomerId($customer->getId())
                    ->setSubscriptionId($subscriptionId)
                    ->setWebsiteId($websiteId)         
                    ->setCcLast4(substr($params['paymentParam']['cc_number'], -4, 4))
                    ->setCreatedAt(date('Y-m-d H:i:s'))
                    ->setUpdatedAt(date('Y-m-d H:i:s'))
                    ->save();
                    $message = '<div id="messages"><div class="messages"><div class="message message-success success"><div data-ui-id="messages-message-success">Credit card saved successfully.</div></div></div></div>';
                    $cyberBlock = $this->_view->getLayout()->createBlock(
                        'Magedelight\Cybersource\Block\Adminhtml\CardTab'
                    );
                    $cyberBlock->setChild('cybersourceAddCards', $this->_view->getLayout()->createBlock(
                        'Magedelight\Cybersource\Block\Adminhtml\CardForm'
                    ));
                    $cyberBlock->setCustomerId($customerId);
                    $append .= $cyberBlock->toHtml();
                    $result = ['error' => false, 'message' => $message, 'append' => $append];
                }
            } else {
                $errorDescription = $this->_errorMessage[$code];
                if ($code == '102' || $code == '101') {
                    if (isset($response->invalidField)) {
                        $errorDescription .= is_array($response->invalidField) ? implode(' ', $response->invalidField) : $response->invalidField;
                    }
                    if (isset($response->missingField)) {
                        $errorDescription .= is_array($response->missingField) ? implode(' ', $response->missingField) : $response->missingField;
                    }
                }
                if (isset($errorDescription) && !empty($errorDescription)) {
                    $message = '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.'Error code: '.$code.' : '.$errorMessage.' : '.$errorDescription.'</div></div></div></div>';
                    $result = ['error' => true, 'message' => $message];
                } else {
                    $message = '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.'Error code: '.$code.' : '.$errorMessage.'</div></div></div></div>';
                    $result = ['error' => true, 'message' => $message];
                }
            }
        } catch (\Exception $e) {
            $message = '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.$e->getMessage().'</div></div></div></div>';
            $result = ['error' => true, 'message' => $message];
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
