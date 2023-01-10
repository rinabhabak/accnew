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

class Save extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    protected $_customer = null;

    protected $_requestObject;
    
    protected $formKeyValidator;

    protected $_customerSession;

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
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customer,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\DataObject $requestObject,
        \Magedelight\Cybersource\Model\Api\Soap $soapModel,
        \Magedelight\Cybersource\Model\Cards $cardModel,
        \Magedelight\Cybersource\Helper\Data $cybersourceHelper,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        $this->_customer            = $customer;
        $this->resultPageFactory    = $resultPageFactory;
        $this->_requestObject       = $requestObject;
        $this->_soapModel           = $soapModel;
        $this->_cardModel           = $cardModel;
        $this->cybersourceHelper    = $cybersourceHelper;
        $this->formKeyValidator     = $formKeyValidator;
        parent::__construct($context);
    }

    public function getCustomer()
    {
        return $this->_customer->getCustomer();
    }

    protected function _getSession()
    {
        return $this->_customer;
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
        $resultPage = $this->resultPageFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();
        $customer_account_scope =  $this->cybersourceHelper->getCustomerAccountScope();
        $websiteId = $this->cybersourceHelper->getWebsiteId();
        
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/listing');
        }
        
        $errorMessage = '';
        $params = $this->getRequest()->getPostValue();
        if (!$params) {
            return $resultRedirect->setPath('*/*/listing');

            return;
        }
        #DebugBreak();
        $mode = $params['magedelight_cybersource']['card_mode'];
        $params['magedelight_cybersource']['address_info']['country_id'] = $params['country_id'];
        $customer = $this->getCustomer();
        try {
            foreach($params['magedelight_cybersource']['address_info'] as $key => $addressInfo){
                $value = strip_tags($addressInfo);
                $params['magedelight_cybersource']['address_info'][$key] = $value;
            }
            $requestObject = $this->_requestObject;

            $requestObject->addData(array(
                'customer_id' => $customer->getId(),
                'email' => $customer->getEmail(),
            ));
            $requestObject->addData($params['magedelight_cybersource']['address_info']);
            $requestObject->addData($params['magedelight_cybersource']['payment_info']);

            $response = $this->_soapModel
            ->setInputData($requestObject)
            ->createCustomerProfile();
            $code = $response->reasonCode;
            if ($code == '100') {
                $subscriptionId = $response->paySubscriptionCreateReply->subscriptionID;
                if (!empty($subscriptionId)) {
                    $model = $this->_cardModel
                    ->setData($params['magedelight_cybersource']['address_info'])
                    ->setCustomerId($customer->getId())
                    ->setSubscriptionId($subscriptionId)
                    ->setWebsiteId($websiteId)        
                    ->setccType($params['magedelight_cybersource']['payment_info']['cc_type'])
                    ->setcc_exp_month($params['magedelight_cybersource']['payment_info']['cc_exp_month'])
                    ->setcc_exp_year($params['magedelight_cybersource']['payment_info']['cc_exp_year'])
                    ->setCcLast4(substr($params['magedelight_cybersource']['payment_info']['cc_number'], -4, 4))
                    ->setCreatedAt(date('Y-m-d H:i:s'))
                    ->setUpdatedAt(date('Y-m-d H:i:s'))
                    ->save();
                    $this->messageManager->addSuccess(__('Credit card saved successfully.'));
                }
            } else {
                $errorMessage = $this->_errorMessage[$code];
                if ($code == '102' || $code == '101') {
                    $errorDescription = '';
                    if (isset($response->invalidField)) {
                        $errorDescription .= is_array($response->invalidField) ? implode(' ', $response->invalidField) : $response->invalidField;
                    }
                    if (isset($response->missingField)) {
                        $errorDescription .= is_array($response->missingField) ? implode(' ', $response->missingField) : $response->missingField;
                    }
                }
                if (isset($errorDescription) && !empty($errorDescription)) {
                    $this->messageManager->addError('Error code: '.$code.' : '.$errorMessage.' : '.$errorDescription);
                } else {
                    $this->messageManager->addError('Error code: '.$code.' : '.$errorMessage);
                }
            }

            return $resultRedirect->setPath('*/*/listing');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage()));

            return $resultRedirect->setPath('*/*/listing');
        }
        return $resultRedirect->setPath('*/*/listing');
    }
}
