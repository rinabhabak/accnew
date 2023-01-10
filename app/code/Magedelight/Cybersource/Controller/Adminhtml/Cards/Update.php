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

use Magedelight\Cybersource\Model\CardsFactory;

class Update extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    protected $_customerFactory = null;
    protected $resultJsonFactory;
    protected $_requestObject;
    protected $cardFactory;

    protected $_customerSession;

    protected $_soapModel;

    protected $_cardModel;

    protected $_directoryHelper;

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
        \Magedelight\Cybersource\Model\Cards $cardModel,
        \Magento\Directory\Helper\Data $directoryHelper,
         CardsFactory $cardFactory
    ) {
        $this->_customerFactory = $customerFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_requestObject = $requestObject;
        $this->_soapModel = $soapModel;
        $this->_cardModel = $cardModel;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_directoryHelper = $directoryHelper;
        $this->_cardFactory = $cardFactory;
        parent::__construct($context);
    }

    public function getCustomer($customerId)
    {
        return $this->_customerFactory->create()->load($customerId);
    }
    public function execute()
    {
        $append = '';
        $errorMessage = '';
        $params = $this->getRequest()->getParams();
        $customerId = $params['id'];
        if ($this->_directoryHelper->isRegionRequired($params['paymentParam']['country_id'])) {
            $params['paymentParam']['state'] = '';
        } else {
            $params['paymentParam']['region_id'] = 0;
        }
        if (!$params) {
            $message = '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.__('Please try again.').'</div></div></div></div>';
            $result = ['error' => true, 'message' => $message];
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($result);

            return $resultJson;
        }
        #DebugBreak();
        $customer = $this->getCustomer($customerId);
        $updateCardId = $params['paymentParam']['card_id'];
        if (!empty($updateCardId)) {
            $cardModel = $this->_cardModel->load($updateCardId);
            if ($cardModel->getId()) {
                $subscriptionId = $cardModel->getData('subscription_id');
                $requestObject = $this->_requestObject;
                $requestObject->addData(array(
                        'customer_id' => $customer->getId(),
                        'customer_subscription_id' => $subscriptionId,
                    ));
                $requestObject->addData($params['paymentParam']);
                $response = $this->_soapModel
                    ->setInputData($requestObject)
                    ->updateCustomerProfile();
                $code = $response->reasonCode;
                $updateResultCode = $response->paySubscriptionUpdateReply->reasonCode;
                if ($code == '100' && $updateResultCode == '100') {
                    $oldCardData = $cardModel->getData();
                    unset($oldCardData['card_id']);
                    try {
                        $newSubscriptionId = $response->paySubscriptionUpdateReply->subscriptionID;
                        $model = $this->_cardFactory->create();
                        $model->load($updateCardId);
                        $model->setData($oldCardData);
                        $model->setData($params['paymentParam']);
                        if ($params['paymentParam']['cc_action'] == 'existing') {
                            $model->setccType($oldCardData['cc_type'])
                                ->setcc_exp_month($oldCardData['cc_exp_month'])
                                ->setcc_exp_year($oldCardData['cc_exp_year']);
                            if (isset($oldCardData['cc_last4'])):
                                    $model->setccLast4($oldCardData['cc_last4']);
                            endif;
                        } else {
                            $model->setccType($params['paymentParam']['cc_type'])
                                ->setccExpMonth($params['paymentParam']['cc_exp_month'])
                                ->setccExpYear($params['paymentParam']['cc_exp_year'])
                                ->setccLast4(substr($params['paymentParam']['cc_number'], -4, 4));
                        }
                        $model->setSubscriptionId($newSubscriptionId)
                            ->setCustomerId($customer->getId())
                            ->setUpdatedAt(date('Y-m-d H:i:s'))
                            ->setCardId($updateCardId);
                        $model->save();
                        $message = '<div id="messages"><div class="messages"><div class="message message-success success"><div data-ui-id="messages-message-success">Card updated successfully.</div></div></div></div>';
                        $cyberBlock = $this->_view->getLayout()->createBlock(
                                'Magedelight\Cybersource\Block\Adminhtml\CardTab'
                            );
                        $cyberBlock->setChild('cybersourceAddCards', $this->_view->getLayout()->createBlock(
                                'Magedelight\Cybersource\Block\Adminhtml\CardForm'
                            ));
                        $cyberBlock->setCustomerId($customerId);
                        $append .= $cyberBlock->toHtml();
                        $result = ['error' => false, 'message' => $message, 'append' => $append];
                    } catch (\Exception $e) {
                        $message = '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.$e->getMessage().'</div></div></div></div>';
                        $result = ['error' => true, 'message' => $message];
                    }
                } else {
                    $errorMessage = $this->_errorMessage[$code];
                    $message = '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.'Error code: '.$code.' : '.$errorMessage.'</div></div></div></div>';
                    $result = ['error' => true, 'message' => $message];
                }
            }
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
