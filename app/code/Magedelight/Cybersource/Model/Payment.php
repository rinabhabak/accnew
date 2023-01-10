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
namespace Magedelight\Cybersource\Model;

use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory as TransactionCollectionFactory;

use Magento\Quote\Api\Data\CartInterface;

class Payment extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'magedelight_cybersource';
    protected $_formBlockType = 'Magedelight\Cybersource\Block\Form';
    protected $_infoBlockType = 'Magedelight\Cybersource\Block\Info';
    const RESPONSE_CODE_SUCCESS = 100;
    const CC_CARDTYPE_SS = 'SS';

    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';
    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
    const REQUEST_TYPE_CREDIT = 'CREDIT';
    const REQUEST_TYPE_VOID = 'VOID';
    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';

    /**
     * Bit masks to specify different payment method checks.
     *
     * @see Mage_Payment_Model_Method_Abstract::isApplicableToQuote
     */
    const CHECK_USE_FOR_COUNTRY = 1;
    const CHECK_USE_FOR_CURRENCY = 2;
    const CHECK_USE_CHECKOUT = 4;
    const CHECK_USE_FOR_MULTISHIPPING = 8;
    const CHECK_USE_INTERNAL = 16;
    const CHECK_ORDER_TOTAL_MIN_MAX = 32;
    const CHECK_RECURRING_PROFILES = 64;
    const CHECK_ZERO_TOTAL = 128;

    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;
    protected $_canReviewPayment = false;
    protected $_canManageRecurringProfiles = false;
    protected $_canFetchTransactionInfo = true;
    protected $_canCapturePartial = true;
    protected $_canRefundInvoicePartial = true;
    protected $request;
    protected $checkoutsession;
    protected $customerSession;
    protected $paymentconfig;
    protected $encryptor;
    protected $_cardsStorage = null;

    protected $_store = 0;
    protected $_customer = null;
    protected $_backend = false;
    protected $_configModel = null;
    protected $_invoice = null;
    protected $_creditmemo = null;
    protected $cardfactory;
    protected $soapmodel;
    protected $regitstry;
    protected $ordermodel;
    protected $_requestHttp;
    protected $cardpayment;
    protected $paymentTrans;
    protected $date;

    const RESPONSE_CODE_APPROVED = 1;
    const RESPONSE_CODE_DECLINED = 2;
    const RESPONSE_CODE_ERROR = 3;
    const RESPONSE_CODE_HELD = 4;

    const RESPONSE_REASON_CODE_APPROVED = 1;
    const RESPONSE_REASON_CODE_NOT_FOUND = 16;
    const RESPONSE_REASON_CODE_PARTIAL_APPROVE = 295;
    const RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED = 252;
    const RESPONSE_REASON_CODE_PENDING_REVIEW = 253;
    const RESPONSE_REASON_CODE_PENDING_REVIEW_DECLINED = 254;

    const PARTIAL_AUTH_CARDS_LIMIT = 5;

    const PARTIAL_AUTH_LAST_SUCCESS = 'last_success';
    const PARTIAL_AUTH_LAST_DECLINED = 'last_declined';
    const PARTIAL_AUTH_ALL_CANCELED = 'all_canceled';
    const PARTIAL_AUTH_CARDS_LIMIT_EXCEEDED = 'card_limit_exceeded';
    const PARTIAL_AUTH_DATA_CHANGED = 'data_changed';
    const TRANSACTION_STATUS_EXPIRED = 'expired';

    protected $_isTransactionFraud = 'is_transaction_fraud';
    protected $_realTransactionIdKey = 'real_transaction_id';
    protected $_isGatewayActionsLockedKey = 'is_gateway_actions_locked';
    protected $_partialAuthorizationLastActionStateSessionKey = 'magedelight_cybersource_last_action_state';
    protected $_partialAuthorizationChecksumSessionKey = 'magedelight_cybersource_checksum';

    protected $_allowCurrencyCode = array();
    protected $_postData = array();

    protected $_errorMessage = array(
            '100' => 'Successful transaction',
            '101' => 'The request is missing one or more required fields',
            '102' => 'One or more fields in the request contains invalid data',
            '104' => 'Resend the request with a unique merchant reference code',
            '110' => 'Only a partial amount was approved',
            '150' => 'General system failure',
            '151' => 'The request was received but there was a server timeout. This error does not include timeouts between the client and the server',
            '152' => 'The request was received, but a service did not finish running in time',
            '200' => 'The authorization request was approved by the issuing bank but declined by CyberSource because it did not pass the Address Verification System check',
            '201' => 'The issuing bank has questions about the request. You will not receive an authorization code programmatically, but you can obtain one verbally by calling the processor',
            '202' => 'Expired card,Request a different card or other form of payment',
            '203' => 'General decline of the card. Request a different card or other form of payment',
            '204' => 'Insufficient funds in the account,Request a different card or other form of payment',
            '205' => 'Stolen or lost card',
            '207' => 'Issuing bank unavailable,Wait a few minutes and resend the request',
            '208' => 'Inactive card or card not authorized for card-not-present transactions,Request a different card or other form of payment',
            '209' => 'CVN did not match',
            '210' => 'The card has reached the credit limit',
            '211' => 'Invalid card verification number',
            '220' => "The processor declined the request based on a general issue with the customer's account",
            '221' => "The customer matched an entry on the processor's negative file,Review the order and contact the payment processor",
            '222' => "The customer's bank account is frozen",
            '230' => 'The authorization request was approved by the issuing bank but declined by CyberSource because it did not pass the CVN check',
            '231' => 'Invalid account number',
            '232' => 'The card type is not accepted by the payment processor',
            '233' => 'General decline by the processor',
            '234' => 'There is a problem with your CyberSource merchant configuration',
            '236' => 'Processor failure',
            '237' => 'The authorization has already been reversed',
            '238' => 'The authorization has already been captured',
            '239' => 'The requested transaction amount must match the previous transaction amount',
            '240' => 'The card type sent is invalid or does not correlate with the card number',
            '241' => 'The request ID is invalid',
            '242' => 'You requested a capture, but there is no corresponding, unused authorization record,Request a new authorization, and if successful, proceed with the capture',
            '243' => 'The transaction has already been settled or reversed',
            '246' => 'The capture or credit is not voidable because the capture or credit information has already been submitted to your processor OR You requested a void for a type of transaction that cannot be voided',
            '247' => 'You requested a credit for a capture that was previously voided',
            '250' => 'The request was received, but there was a timeout at the payment processor,do not resend the request until you have reviewed the transaction status in the Business Center',
            '254' => 'Stand-alone credits are not allowed',
            '400' => 'Fraud score exceeds threshold',
            '450' => 'Apartment number missing or not found',
            '451' => 'Insufficient address information',
            '452' => 'House/Box number not found on street',
            '453' => 'Multiple address matches were found',
            '454' => 'P.O. Box identifier not found or out of range',
            '455' => 'Route service identifier not found or out of range',
            '456' => 'Street name not found in Postal code',
            '457' => 'Postal code not found in database',
            '458' => 'Unable to verify or correct address',
            '459' => 'Multiple addres matches were found (international)',
            '460' => 'Address match not found (no reason given)',
            '461' => 'Unsupported character set',
            '475' => 'The cardholder is enrolled in Payer Authentication. Please authenticate the cardholder before continuing with the transaction',
            '476' => 'Encountered a Payer Authentication problem. Payer could not be authenticated',
            '480' => 'The order is marked for review by Decision Manager',
            '481' => 'The order has been rejected by Decision Manager',
            '520' => 'The authorization request was approved by the issuing bank but declined by CyberSource based on your Smart Authorization settings',
            '700' => 'The customer matched the Denied Parties List',
            '701' => 'Export bill_country/ship_country match',
            '702' => 'Export email_country match',
            '703' => 'Export hostname_country/ip_country match',
        );
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        TransactionCollectionFactory $salesTransactionCollectionFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
         \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magedelight\Cybersource\Model\Config $cybersourceConfig,
        \Magedelight\Cybersource\Helper\Data $cybersourceHelper,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectFactory,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Magedelight\Cybersource\Model\Api\Soap $soapmodel,
        \Magento\Sales\Model\Order $ordermodel,
        \Magento\Payment\Model\Config $paymentconfig,
        CardsFactory $cardFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Model\Order\Payment\Transaction $paymentTrans,
         \Magedelight\Cybersource\Model\Payment\Cards $cardpayment,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $requestHttp,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
           $context,
           $registry,
           $extensionFactory,
           $customAttributeFactory,
           $paymentData,
           $scopeConfig,
           $logger,
           $moduleList,
           $localeDate,
           $resource,
           $resourceCollection,
           $data
        );
        $this->storeManager = $storeManager;
        $this->cybersourceHelper = $cybersourceHelper;
        $this->cybersourceConfig = $cybersourceConfig;
        $this->salesTransactionCollectionFactory = $salesTransactionCollectionFactory;
        $this->productMetaData = $productMetaData;
        $this->regionFactory = $regionFactory;
        $this->orderRepository = $orderRepository;
        $this->objectFactory = $objectFactory;
        $this->cardfactory = $cardFactory;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->checkoutsession = $checkoutsession;
        $this->soapmodel = $soapmodel;
        $this->ordermodel = $ordermodel;
        $this->paymentconfig = $paymentconfig;
        $this->_requestHttp = $requestHttp;
        $this->encryptor = $encryptor;
        $this->date = $date;
        $this->cardpayment = $cardpayment;
        $this->paymentTrans = $paymentTrans;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->_backend = $this->cybersourceConfig->checkAdmin() ? true : false;
        if ($this->_backend && $this->_registry->registry('current_order')) {
            $this->setStore($this->_registry->registry('current_order')->getStoreId());
        } elseif ($this->_backend && $this->_registry->registry('current_invoice')) {
            $this->setStore($this->_registry->registry('current_invoice')->getStoreId());
        } elseif ($this->_backend && $this->_registry->registry('current_creditmemo')) {
            $this->setStore($this->_registry->registry('current_creditmemo')->getStoreId());
        } elseif ($this->_backend && $this->_registry->registry('current_customer') != false) {
            $this->setStore($this->_registry->registry('current_customer')->getStoreId());
        } elseif ($this->_backend && $this->objectFactory->get('Magento\Backend\Model\Session\Quote')->getStoreId() > 0) {
            $this->setStore($this->objectFactory->get('Magento\Backend\Model\Session\Quote')->getStoreId());
        } else {
            $this->setStore($this->storeManager->getStore()->getId());
        }
    }

    public function setStore($id)
    {
         $this->_storeId = $this->cybersourceConfig->getStoreId();
         return $this;
    }

    public function setCustomer($customer)
    {
        $this->_customer = $customer;
        if ($customer->getStoreId() > 0) {
            $this->setStore($customer->getStoreId());
        }

        return $this;
    }
    public function getCustomer()
    {
        if (isset($this->_customer)) {
            $customer = $this->_customer;
        } elseif ($this->_backend) {
            $customer = $this->objectFactory->create('Magento\Customer\Model\Customer')->load($this->objectFactory->get('Magento\Backend\Model\Session\Quote')->getCustomerId());
        } else {
            $customerid = $this->checkoutsession->getQuote()->getCustomerId();
            if (is_null($customerid)){
                 $customer = $this->customerSession->getCustomer();
            }else{
                $customer = $this->customerSession->getCustomer();
            }
        }

        $this->setCustomer($customer);

        return $customer;
    }

    public function getSubscriptionCardInfo($subscriptionId = null)
    {
        $card = null;
        $customer = $this->getCustomer();
        if (!is_null($subscriptionId)) {
            $cardModel = $this->cardfactory->create();
            $card = $cardModel->getCollection()
                ->addFieldToFilter('subscription_id', $subscriptionId)
                ->getData()
                ;
        }

        return $card;
    }
    public function saveCustomerProfileData($subscriptionId, $payment, $customerid = null)
    {

        $websiteId = $this->cybersourceHelper->getWebsiteId();
        if (empty($customerid)) {
            $post = $this->_postData;
            $customer = $this->getCustomer();
            $customerid = $customer->getId();
            $ccType = $post['cc_type'];
            $ccExpMonth = $post['expiration'];
            $ccExpYear = $post['expiration_yr'];
            $ccLast4 = substr($post['cc_number'], -4, 4);
        } else {
            $ccType = $payment->getCcType();
            $ccExpMonth = $payment->getCcExpMonth();
            $ccExpYear = $payment->getCcExpYear();
            $ccLast4 = $payment->getCcLast4();
        }

        if (!empty($subscriptionId) && $customerid) {
            $billing = $payment->getOrder()->getBillingAddress();
            $post = $this->_postData;
            try {
                $model = $this->cardfactory->create();
                $model->setFirstname($billing->getFirstname())
                        ->setLastname($billing->getLastname())
                        ->setPostcode($billing->getPostcode())
                        ->setCountryId($billing->getCountryId())
                        ->setRegionId($billing->getRegionId())
                        ->setState($billing->getRegion())
                        ->setCity($billing->getCity())
                        ->setCompany($billing->getCompany())
                        ->setStreet($billing->getStreet()[0])
                        ->setTelephone($billing->getTelephone())
                        ->setCustomerId($customerid)
                        ->setSubscriptionId($subscriptionId)
                        ->setccType($ccType)
                        ->setcc_exp_month($ccExpMonth)
                        ->setcc_exp_year($ccExpYear)
                        ->setcc_last4($ccLast4)
                        ->setWebsiteId($websiteId)
                        ->setCreatedAt($this->date->gmtDate())
                        ->setUpdatedAt($this->date->gmtDate())
                        ->save();
                    return;
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to save customer profile due to: %1', $e->getMessage()));
            }
        }
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        $post = $data->getData()['additional_data'];
        if (empty($this->_postData)) {
            $this->_postData = $post;
        }
        $this->_registry->register('postdata', $this->_postData);
        if (isset($post['subscription_id']) && $post['subscription_id'] != 'new') {
            $subScriptionIdCheck = $this->encryptor->decrypt($post['subscription_id']);
            $creditCard = $this->getSubscriptionCardInfo($subScriptionIdCheck);
            if ($creditCard != '' && !empty($creditCard)) {
                $this->getInfoInstance()->setCcLast4($creditCard[0]['cc_last_4'])
                    ->setCcType($creditCard[0]['cc_type'])
                    ->setAdditionalInformation('magedelight_cybersource_subscription_id', $subScriptionIdCheck, 'magedelight_save_card', false);
                if (isset($post['cc_cid'])) {
                    $this->getInfoInstance()->setCcCid($post['cc_cid']);
                }
            }
            unset($this->_postData['cc_type']);
            unset($this->_postData['cc_number']);
            unset($this->_postData['expiration']);
            unset($this->_postData['expiration_yr']);
            $this->_registry->unregister('postdata');
            $this->_registry->register('postdata', $this->_postData);
        } else {
            /* code for create token */
            $subscriptionId = $this->getGeneratedSubscriptionId($post);
            $this->getInfoInstance()->setCcType($post['cc_type'])
                ->setCcLast4(substr($post['cc_number'], -4))
                ->setCcExpMonth($post['expiration'])
                ->setCcExpYear($post['expiration_yr'])
                ->setAdditionalInformation('magedelight_save_card', $post['save_card']);
            $this->getInfoInstance()->setAdditionalInformation('magedelight_cybersource_subscription_id', $subscriptionId);
            if (isset($post['cc_cid'])) {
                $this->getInfoInstance()->setCcCid($post['cc_cid']);
            }
            $this->checkoutsession->setSaveCardFlag($post['save_card']);
        }

        return $this;
    }

    public function getGeneratedSubscriptionId($post)
    {
        $payment = $this->getInfoInstance();
        $quote = $payment->getQuote();
        $billingAddress = $quote->getBillingAddress();
        $params = [
                'firstname' => $billingAddress->getFirstname(),
                'lastname' => $billingAddress->getLastname(),
                'company' => $billingAddress->getCompany(),
                'street' => (is_array($billingAddress->getStreet()))?implode(" ",$billingAddress->getStreet()):$billingAddress->getStreet(),
                'city' => $billingAddress->getCity(),
                'region_id' => $billingAddress->getRegionId(),
                'state' => $billingAddress->getRegion(),
                'postcode' => $billingAddress->getPostcode(),
                'telephone' => $billingAddress->getTelephone(),
                'country_id' => $billingAddress->getCountryId(),
                'email' => $quote->getCustomerEmail(),
                'cc_type' => $post['cc_type'],
                'cc_number' => $post['cc_number'],
                'cc_exp_month' => $post['expiration'],
                'cc_exp_year' => $post['expiration_yr'],
                'cc_cid' => (isset($post['cc_cid']) )? $post['cc_cid']:''
            ];
        $requestObject = $this->dataObjectFactory->create();
        $requestObject->addData($params);
        $response = $this->soapmodel
            ->setInputData($requestObject)
            ->createCustomerProfile();
        $code = $response->reasonCode;
        if ($code == '100') {
            $subscriptionId = $response->paySubscriptionCreateReply->subscriptionID;
            return $subscriptionId;
        }
        else{
            throw new \Magento\Framework\Exception\LocalizedException(__("Something Went Wrong."));
        }
    }

    public function validate()
    {
        if (empty($this->_postData)) {
            $this->_postData = $this->_registry->registry('postdata');
        }
        $post = $this->_postData;

        if ($post['subscription_id'] == 'new' && !empty($post['cc_number'])) {
            try {
                $this->parentValidate();
                $info = $this->getInfoInstance();
                $errorMsg = false;
                $availableTypes = explode(',', $this->getConfigData('cctypes'));

                $ccNumber = $info->getCcNumber();
                $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
                $info->setCcNumber($ccNumber);
                $ccType = '';
                if (in_array($info->getCcType(), $availableTypes)) {
                    if ($this->validateCcNum(
                                $ccNumber
                            ) || $this->otherCcType(
                                $info->getCcType()
                            ) && $this->validateCcNumOther(
                                $ccNumber
                            )
                            ) {
                        $ccTypeRegExpList = [
                                    'SO' => '/(^(6334)[5-9](\d{11}$|\d{13,14}$))|(^(6767)(\d{12}$|\d{14,15}$))/',
                                    'SM' => '/(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)'.
                                    '|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)'.
                                    '|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))'.
                                    '|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))'.
                                    '|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))/',
                                    // Visa
                                    'VI' => '/^4[0-9]{12}([0-9]{3})?$/',
                                    // Master Card
                                    'MC' => '/^(5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/',
                                    // American Express
                                    'AE' => '/^3[47][0-9]{13}$/',
                                    // Discover
                                    'DI' => '/^6(?:011|5[0-9]{2})[0-9]{12}$/',
                                    // JCB
                                    'JCB' => '/^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})|36[0-9]{12}'.
                                    '|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}'.
                                    '|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}'.
                                    '|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}'.
                                    '|5[0-9]{14}))$/',
                                    'DC' => '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
                                    'MAESTRO' => '/(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)'.
                                    '|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)'.
                                    '|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))'.
                                    '|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))'.
                                    '|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))/',
                                    'SWITCH' => '/(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)'.
                                    '|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)'.
                                    '|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))'.
                                    '|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))'.
                                    '|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))/',
                                ];

                        $ccNumAndTypeMatches = isset(
                                    $ccTypeRegExpList[$info->getCcType()]
                                ) && preg_match(
                                    $ccTypeRegExpList[$info->getCcType()],
                                    $ccNumber
                                );
                        $ccType = $ccNumAndTypeMatches ? $info->getCcType() : 'OT';

                        if (!$ccNumAndTypeMatches && !$this->otherCcType($info->getCcType())) {
                            $errorMsg = __('The credit card number doesn\'t match the credit card type.');
                        }
                    } else {
                        $errorMsg = __('Invalid Credit Card Number');
                    }
                } else {
                    $errorMsg = __('This credit card type is not allowed for this payment method.');
                }
                        if ($errorMsg === false && $this->hasVerification()) {
                            $verifcationRegEx = $this->getVerificationRegEx();
                            $regExp = isset($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
                            if (!$info->getCcCid() || !$regExp || !preg_match($regExp, $info->getCcCid())) {
                                $errorMsg = __('Please enter a valid credit card verification number.');
                            }
                        }

                if ($ccType != 'SS' && !$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
                    $errorMsg = __('Please enter a valid credit card expiration date.');
                }

                if ($errorMsg) {
                    throw new \Magento\Framework\Exception\LocalizedException($errorMsg);
                }

                return $this;
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException($e->getMessage());
            }
        } else {
            return true;
        }
    }
    /**
     * @param string $type
     * @return bool
     * @api
     */
    public function otherCcType($type)
    {
        return $type == 'OT';
    }
    public function getVerificationRegEx()
    {
        $verificationExpList = [
                'VI' => '/^[0-9]{3}$/',
                'MC' => '/^[0-9]{3}$/',
                'AE' => '/^[0-9]{4}$/',
                'DI' => '/^[0-9]{3}$/',
                'SS' => '/^[0-9]{3,4}$/',
                'SM' => '/^[0-9]{3,4}$/',
                'SO' => '/^[0-9]{3,4}$/',
                'OT' => '/^[0-9]{3,4}$/',
                'JCB' => '/^[0-9]{3,4}$/',
                'MAESTRO' => '/^[0-9]{3}$/',
                'SWITCH' => '/^[0-9]{3}$/',
                'DC' => '/^[0-9]{3}$/',
            ];

        return $verificationExpList;
    }
    public function parentValidate()
    {
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof \Magento\Sales\Model\Order\Payment) {
            $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
        } else {
            $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
        }
        if (!$this->canUseForCountry($billingCountry)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t use the payment type you selected to make payments to the billing country.')
                );
        }

        return $this;
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $exceptionMessage = false;
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for authorization.'));
        }
        $this->_initCardsStorage($payment);
        if (empty($this->_postData)) {
            $this->_postData = $this->_registry->registry('postdata');
        }
        $post = $this->_postData;
        try {
            $isMultiShipping = $this->checkoutsession->getQuote()->getData('is_multi_shipping');
            $subScriptionIdCheck = $payment->getData('additional_information', 'magedelight_cybersource_subscription_id');
            if ((!empty($subScriptionIdCheck) && empty($post['cc_number'])) || ($isMultiShipping == '1' && !empty($subScriptionIdCheck))) { // magedelight order using subscription id
               $payment->setMagedelightcybersourceSubscriptionId($subScriptionIdCheck);
               $response = $this->soapmodel
                    ->prepareAuthorizeResponse($payment, $amount, true);
            } else {
                $response = $this->soapmodel
                    ->prepareAuthorizeResponse($payment, $amount, false);
            }

            if ($response->reasonCode == self::RESPONSE_CODE_SUCCESS) {
                $quote = $this->checkoutsession->getQuote();
                if (!empty($subScriptionIdCheck) && empty($post['cc_number'])) {
                    $card = $this->getSubscriptionCardInfo($subScriptionIdCheck);
                    if($card != null){
                        $payment->setCcLast4($card[0]['cc_last_4']);
                        $payment->setCcType($card[0]['cc_type']);
                        $payment->setAdditionalInformation('magedelight_cybersource_subscription_id', $subScriptionIdCheck);
                        $payment->setMagedelightcybersourceSubscriptionid($subScriptionIdCheck);
                    }
                } else {
                    $payment->setCcLast4(substr($post['cc_number'], -4, 4));
                    $payment->setCcType($post['cc_type']);
                }
                $saveCard = $payment->getData('additional_information', 'magedelight_save_card');
                if (($saveCard == 'true' && isset($post['cc_number']))
                    && $post['cc_number'] != '' && ($this->customerSession->getCustomerId()
                        || ($this->cybersourceHelper->checkAdmin() &&
                            $this->objectFactory->get('Magento\Backend\Model\Session\Quote')->getQuote()->getCustomerId()))) {
                    $profileResponse = $this->soapmodel
                        ->createCustomerProfileFromTransaction($response->requestID);
                    $code = $profileResponse->reasonCode;
                    $profileResponsecheck = $profileResponse->paySubscriptionCreateReply->reasonCode;
                    if ($code == '100' && $profileResponsecheck == '100') {
                        $customerid = $this->cybersourceHelper->checkAdmin() ? $this->objectFactory->get('Magento\Backend\Model\Session\Quote')->getQuote()->getCustomerId() : $this->customerSession->getCustomer()->getId();
                        $subscriptionId = $profileResponse->paySubscriptionCreateReply->subscriptionID;
                        $this->saveCustomerProfileData($subscriptionId, $payment, $customerid);
                    } else {
                        $errorMessage = $this->_errorMessage[$code];
                        if ($code == '102' || $code == '101') {
                            if (isset($response->invalidField)) {
                                $errorMessage .= is_array($response->invalidField) ? implode(' ', $response->invalidField) : $response->invalidField;
                            }
                            if (isset($response->missingField)) {
                                $errorMessage .= is_array($response->missingField) ? implode(' ', $response->missingField) : $response->missingField;
                            }
                            $errorMessage = is_array($errorMessage) ? implode(',', $errorMessage) : $errorMessage;
                            $errorMessage = $errorMessage.' , '.$this->_errorMessage[$code];
                        }
                        if (isset($errorMessage) && !empty($errorMessage)) {
                            throw new \Magento\Framework\Exception\LocalizedException(__('Error code: '.$code.' : '.$errorMessage));
                        } else {
                            throw new \Magento\Framework\Exception\LocalizedException(__('Error code: '.$code.' : '.$errorMessage));
                        }
                    }
                }
                elseif ($saveCard == 'true' && $payment->getAdditionalInformation('magedelight_cybersource_subscription_id')!=''){
                    $order = $payment->getOrder();
                    $customerid = $order->getCustomerId();
                    $subscriptionId = $payment->getAdditionalInformation('magedelight_cybersource_subscription_id');
                    $this->saveCustomerProfileData($subscriptionId, $payment, $customerid);
                }
                $csToRequestMap = self::REQUEST_TYPE_AUTH_ONLY;
                $payment->setAnetTransType($csToRequestMap);
                $payment->setAmount($amount);
                $newTransactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH;
                $card = $this->_registerCard($response, $payment);

                $this->_addTransaction(
                                $payment,
                                $response->requestID,
                                $newTransactionType,
                                array('is_transaction_closed' => 0),
                                array($this->_realTransactionIdKey => $response->requestID),
                                $this->cybersourceHelper->getTransactionMessage(
                                    $payment, $csToRequestMap, $response->requestID, $card, $amount
                                )
                            );

                $card->setLastTransId($response->requestID);

                $payment->setLastTransId($response->requestID)
                    ->setCcTransId($response->requestID)
                    ->setTransactionId($response->requestID)
                    ->setmagedelightCybersourceRequestid($response->requestID)
                    ->setCybersourceToken($response->requestToken)
                    ->setIsTransactionClosed(0)
                    ->setStatus(self::STATUS_APPROVED)
                    ->setCcAvsStatus($response->ccAuthReply->avsCode);
                    /*
                    * checking if we have cvCode in response bc
                    * if we don't send cvn we don't get cvCode in response
                    */
                    if (isset($response->ccAuthReply->cvCode)) {
                        $payment->setCcCidStatus($response->ccAuthReply->cvCode);
                    }
            } else {
                $card = $this->_registerCard($response, $payment);
                $resonCode = $response->reasonCode;

                if ($resonCode == '102' || $resonCode == '101') {
                    $exceptionMessage = '';
                    if (isset($response->invalidField)) {
                        $exceptionMessage .= is_array($response->invalidField) ? implode($response->invalidField) : $response->invalidField;
                    }
                    if (isset($response->missingField)) {
                        $exceptionMessage .= is_array($response->missingField) ? implode($response->missingField) : $response->missingField;
                    }

                    $exceptionMessage = is_array($exceptionMessage) ? implode(',', $exceptionMessage) : $exceptionMessage;
                    $exceptionMessage = empty($exceptionMessage) ? $this->_errorMessage[$resonCode] : $exceptionMessage;
                    $exceptionMessage = $this->_wrapGatewayError($exceptionMessage);
                    $exceptionMessage = $exceptionMessage.' , '.$this->_errorMessage[$resonCode];
                } else {
                    $exceptionMessage = $this->_wrapGatewayError($this->_errorMessage[$resonCode]);
                }

                $exceptionMessage = $this->cybersourceHelper->getTransactionMessage(
                        $payment, self::REQUEST_TYPE_AUTH_ONLY, $response->requestID, $card, $amount, $exceptionMessage
                    );
                throw new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage));
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cybersource Gateway request error: '. $e->getMessage()));
        }
        if ($exceptionMessage !== false) {
            throw new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage));
        }
        $payment->setSkipTransactionCreation(true);

        return $this;
    }
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for capture.'));
        }
        $this->_initCardsStorage($payment);

        if (empty($this->_postData)) {
            $this->_postData = $this->_registry->registry('postdata');
        }
        $post = $this->_postData;
        try {
            if ($this->_isPreauthorizeCapture($payment)) {
                $this->_preauthorizeCapture($payment, $amount);
            } else {
                $isMultiShipping = $this->checkoutsession->getQuote()->getData('is_multi_shipping');
                $subScriptionIdCheck = $payment->getData('additional_information', 'magedelight_cybersource_subscription_id');
                if ((!empty($subScriptionIdCheck) && empty($post['cc_number'])) || ($isMultiShipping == '1' && !empty($subScriptionIdCheck))) { // magedelight order using subscription id
                    $payment->setMagedelightcybersourceSubscriptionId($subScriptionIdCheck);
                    $response = $this->soapmodel
                        ->prepareCaptureResponse($payment, $amount, true);
                } else {
                    $response = $this->soapmodel
                        ->prepareCaptureResponse($payment, $amount, false);
                }

                if ($response->reasonCode == self::RESPONSE_CODE_SUCCESS) {
                    $quote = $this->checkoutsession->getQuote();
                    if (!empty($subScriptionIdCheck) && empty($post['cc_number'])) {
                        $card = $this->getSubscriptionCardInfo($subScriptionIdCheck);
                        if($card!=null){
                            $payment->setCcLast4($card[0]['cc_last_4']);
                            $payment->setCcType($card[0]['cc_type']);
                            $payment->setAdditionalInformation('magedelight_cybersource_subscription_id', $subScriptionIdCheck);
                            $payment->setMagedelightcybersourceSubscriptionid($subScriptionIdCheck);
                        }
                    } else {
                        $payment->setCcLast4(substr($post['cc_number'], -4, 4));
                        $payment->setCcType($post['cc_type']);
                    }
                    $saveCard = $payment->getData('additional_information', 'magedelight_save_card');
                    if (($saveCard == 'true' && isset($post['cc_number'])) && $post['cc_number'] != ''
                        && ($this->customerSession->getCustomerId() || ($this->cybersourceHelper->checkAdmin()
                        && $this->objectFactory->get('Magento\Backend\Model\Session\Quote')->getQuote()->getCustomerId()))) {
                            $profileResponse = $this->soapmodel
                            ->createCustomerProfileFromTransaction($response->requestID);
                        $code = $profileResponse->reasonCode;
                        $profileResponsecheck = $profileResponse->paySubscriptionCreateReply->reasonCode;
                        if ($code == '100' && $profileResponsecheck == '100') {
                            $customerid = $this->cybersourceHelper->checkAdmin() ? $this->objectFactory->get('Magento\Backend\Model\Session\Quote')->getQuote()->getCustomerId() : $this->customerSession->getCustomer()->getId();
                            $subscriptionId = $profileResponse->paySubscriptionCreateReply->subscriptionID;
                            $this->saveCustomerProfileData($subscriptionId, $payment, $customerid);
                        } else {
                            $errorMessage = $this->_errorMessage[$code];
                            if ($code == '102' || $code == '101') {
                                if (isset($response->invalidField)) {
                                    $errorMessage .= is_array($response->invalidField) ? implode(' ', $response->invalidField) : $response->invalidField;
                                }
                                if (isset($response->missingField)) {
                                    $errorMessage .= is_array($response->missingField) ? implode(' ', $response->missingField) : $response->missingField;
                                }
                                $errorMessage = is_array($errorMessage) ? implode(',', $errorMessage) : $errorMessage;
                                $errorMessage = $errorMessage.' , '.$this->_errorMessage[$code];
                            }
                            if (isset($errorMessage) && !empty($errorMessage)) {
                                throw new \Magento\Framework\Exception\LocalizedException(__($errorMessage));
                            }
                        }
                    }

                    $card = $this->_registerCard($response, $payment);
                    $csToRequestMap = self::REQUEST_TYPE_AUTH_CAPTURE;
                    $newTransactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
                    $this->_addTransaction(
                                $payment,
                                $response->requestID,
                                $newTransactionType,
                                array('is_transaction_closed' => 0),
                                array($this->_realTransactionIdKey => $response->requestID),
                                $this->cybersourceHelper->getTransactionMessage(
                                    $payment, $csToRequestMap, $response->requestID, $card, $amount
                                )
                            );
                    $card->setLastTransId($response->requestID);
                    $card->setCapturedAmount($card->getProcessedAmount());
                    $captureTransactionId = $response->requestID;
                    $card->setLastCapturedTransactionId($captureTransactionId);
                    $this->getCardsStorage()->updateCard($card);

                    $payment->setLastTransId($response->requestID)
                        ->setLastCybersourceToken($response->requestToken)
                        ->setCcTransId($response->requestID)
                        ->setTransactionId($response->requestID)
                        ->setIsTransactionClosed(0)
                        ->setCybersourceToken($response->requestToken)
                        ;
                } else {
                    $card = $this->_registerCard($response, $payment);
                    $resonCode = $response->reasonCode;
                    $exceptionMessage = '';
                    if ($resonCode == '102' || $resonCode == '101') {
                        if (isset($response->invalidField)) {
                            $exceptionMessage .= is_array($response->invalidField) ? implode(' ', $response->invalidField) : $response->invalidField;
                        }
                        if (isset($response->missingField)) {
                            $exceptionMessage .= is_array($response->missingField) ? implode(' ', $response->missingField) : $response->missingField;
                        }
                        $exceptionMessage = is_array($exceptionMessage) ? implode(',', $exceptionMessage) : $exceptionMessage;
                        $exceptionMessage = empty($exceptionMessage) ? $this->_errorMessage[$resonCode] : $exceptionMessage;
                        $exceptionMessage = $this->_wrapGatewayError($exceptionMessage);
                    } else {
                        $exceptionMessage = $this->_wrapGatewayError($this->_errorMessage[$resonCode]);
                    }
                    $exceptionMessage = $this->cybersourceHelper->getTransactionMessage(
                            $payment, self::REQUEST_TYPE_AUTH_CAPTURE, $response->requestID, $card, $amount, $exceptionMessage
                        );
                    throw new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage));
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Gateway request error: %1', $e->getMessage()));
        }
        $payment->setSkipTransactionCreation(true);

        return $this;
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $cardsStorage = $this->getCardsStorage($payment);
        if ($this->_formatAmount(
                $cardsStorage->getCapturedAmount() - $cardsStorage->getRefundedAmount()
                ) < $amount
            ) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for refund.'));
        }
        $messages = array();
        $isSuccessful = false;
        $isFiled = false;
            // Grab the invoice in case partial invoicing
            $creditmemo = $this->registry->registry('current_creditmemo');
        if (!is_null($creditmemo)) {
            $this->_invoice = $creditmemo->getInvoice();
        }
        foreach ($cardsStorage->getCards() as $card) {
            $lastTransactionId = $payment->getData('cc_trans_id');
            $cardTransactionId = $card->getTransactionId();

            if ($lastTransactionId == $cardTransactionId) {
                if ($amount > 0) {
                    $cardAmountForRefund = $this->_formatAmount($card->getCapturedAmount() - $card->getRefundedAmount());
                    if ($cardAmountForRefund <= 0) {
                        continue;
                    }
                    if ($cardAmountForRefund > $amount) {
                        $cardAmountForRefund = $amount;
                    }
                    try {
                        $newTransaction = $this->_refundCardTransaction($payment, $cardAmountForRefund, $card);
                        if ($newTransaction != null) {
                            $messages[] = $newTransaction->getMessage();
                            $isSuccessful = true;
                        }
                    } catch (\Exception $e) {
                        $messages[] = $e->getMessage();
                        $isFiled = true;
                        continue;
                    }
                    $card->setRefundedAmount($this->_formatAmount($card->getRefundedAmount() + $cardAmountForRefund));
                    $cardsStorage->updateCard($card);
                    $amount = $this->_formatAmount($amount - $cardAmountForRefund);
                } else {
                    $payment->setSkipTransactionCreation(true);

                    return $this;
                }
            }
        }

        if ($isFiled) {
            $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
        }

        $payment->setSkipTransactionCreation(true);

        return $this;
    }
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        $cardsStorage = $this->getCardsStorage($payment);
        $messages = array();
        $isSuccessful = false;
        $isFiled = false;
        foreach ($cardsStorage->getCards() as $card) {
            $lastTransactionId = $payment->getData('cc_trans_id');
            $cardTransactionId = $card->getTransactionId();
            if ($lastTransactionId == $cardTransactionId) {
                try {
                    $newTransaction = $this->_voidCardTransaction($payment, $card);
                    if ($newTransaction != null) {
                        $messages[] = $newTransaction->getMessage();
                        $isSuccessful = true;
                    }
                } catch (\Exception $e) {
                    $messages[] = $e->getMessage();
                    $isFiled = true;
                    continue;
                }
                $cardsStorage->updateCard($card);
            }
        }
        if ($isFiled) {
            $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
        }

        $payment->setSkipTransactionCreation(true);

        return $this;
    }
    protected function _voidCardTransaction($payment, $card)
    {
        $authTransactionId = $card->getLastTransId();
        if ($payment->getCcTransId()) {
            $realAuthTransactionId = $payment->getTransactionId();
            $payment->setAnetTransType(self::REQUEST_TYPE_VOID);
            $payment->setTransId($realAuthTransactionId);
            $response = $this->soapmodel
                ->prepareVoidResponse($payment, $card);
            if ($response->reasonCode == self::RESPONSE_CODE_SUCCESS) {
                $voidTransactionId = $response->requestID.'-void';
                $card->setLastTransId($voidTransactionId);
                $payment->setTransactionId($response->requestID)
                    ->setCybersourceToken($response->requestToken)
                    ->setIsTransactionClosed(1);

                $this->_addTransaction(
                                $payment,
                                $voidTransactionId,
                                \Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID,
                                array(
                                    'is_transaction_closed' => 1,
                                    'should_close_parent_transaction' => 1,
                                    'parent_transaction_id' => $authTransactionId,
                                ),
                                array($this->_realTransactionIdKey => $response->requestID),
                                $this->cybersourceHelper->getTransactionMessage(
                                    $payment, self::REQUEST_TYPE_VOID, $response->requestID, $card
                                )
                            );
            } else {
                $code = $response->reasonCode;
                $errorMessage = $this->_errorMessage[$code];
                $exceptionMessage = $this->cybersourceHelper->getTransactionMessage(
                            $payment, self::REQUEST_TYPE_VOID, $realAuthTransactionId, $card, false, $errorMessage
                        );
                throw new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage));
            }
        } else {
            return;
        }
    }
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->void($payment);
    }
       /**
        * Payment method available? Yes.
        */
       public function getConfigModel()
       {
           return $this->cybersourceConfig;
       }
    public function isAvailable(CartInterface $quote = null)
    {
        $checkResult = new \StdClass();
        $isActive = $this->getConfigModel()->getIsActive();
        $checkResult->isAvailable = $isActive;
        $checkResult->isDeniedInConfig = !$isActive;
        if ($checkResult->isAvailable && $quote) {
            $checkResult->isAvailable = $this->isApplicableToQuote($quote, self::CHECK_RECURRING_PROFILES);
        }

         return parent::isAvailable($quote);
    }

    protected function _isPreauthorizeCapture(\Magento\Sales\Model\Order\Payment $payment)
    {
        if ($this->getCardsStorage()->getCardsCount() <= 0) {
            return false;
        }
        foreach ($this->getCardsStorage()->getCards() as $card) {
            $lastTransactionId = $payment->getData('cc_trans_id');
            $cardTransactionId = $card->getTransactionId();
            if ($lastTransactionId == $cardTransactionId) {
                if ($payment->getCcTransId()) {
                    return true;
                }

                return false;
            }
        }
    }

    public function getCardsStorage($payment = null)
    {
        if (is_null($payment)) {
            $payment = $this->getInfoInstance();
        }
        if (is_null($this->_cardsStorage)) {
            $this->_initCardsStorage($payment);
        }

        return $this->_cardsStorage;
    }
    protected function _initCardsStorage($payment)
    {
        $this->_cardsStorage = $this->cardpayment->setPayment($payment);
    }

    protected function _registerCard($response, \Magento\Sales\Model\Order\Payment $payment)
    {
        $cardsStorage = $this->getCardsStorage($payment);
        $card = $cardsStorage->registerCard();
        $subscriptionid = $payment->getData('additional_information', 'magedelight_cybersource_subscription_id');
        if ($subscriptionid != '') {
            $customerCard = $this->getSubscriptionCardInfo($subscriptionid);
            if(empty($customerCard)){
                $card->setCcType($payment->getCcType())
                    ->setCcLast4($payment->getCcLast4())
                    ->setCcExpMonth($payment->getCcExpMonth())
                    ->setCcExpYear($payment->getCcExpYear());
            }
            else{
                $card->setCcType($customerCard[0]['cc_type'])
                ->setCcLast4($customerCard[0]['cc_last_4'])
                ->setCcExpMonth($customerCard[0]['cc_exp_month'])
                ->setCcOwner($customerCard[0]['firstname'])
                ->setCcExpYear($customerCard[0]['cc_exp_year']);
            }
            
        } else {
            if (empty($this->_postData)) {
                $this->_postData = $this->_registry->registry('postdata');
            }
            $post = $this->_postData;

            $card->setCcType($post['cc_type'])
                ->setCcLast4(substr($post['cc_number'], -4, 4))
                ->setCcExpMonth($post['expiration'])
                ->setCcExpYear($post['expiration_yr']);
        }

        $card
            ->setLastTransId($response->requestID)
            ->setTransactionId($response->requestID);
             if ($response->reasonCode == self::RESPONSE_CODE_SUCCESS) {
                 $card
                    ->setMerchantReferenceCode($response->merchantReferenceCode)
                    ->setRequestedAmount($response->ccAuthReply->amount)
                    ->setProcessedAmount($response->ccAuthReply->amount)
                    ->setauthorizationCode($response->ccAuthReply->authorizationCode);
                     if(isset($response->ccAuthReply->reconciliationID))
                     {
                         $card->setreconciliationID($response->ccAuthReply->reconciliationID);
                     }
                     if(isset($response->ccAuthReply->avsCode))
                     {
                         $card->setAvsResultCode($response->ccAuthReply->avsCode);
                     }


             }

        $cardsStorage->updateCard($card);

        return $card;
    }
    protected function _preauthorizeCapture($payment, $requestedAmount)
    {
        $cardsStorage = $this->getCardsStorage($payment);
        if ($this->_formatAmount(
                $cardsStorage->getProcessedAmount() - $cardsStorage->getCapturedAmount()
                ) < $requestedAmount
            ) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for capture.'));
        }
        $messages = array();
        $isSuccessful = false;
        $isFiled = false;
        foreach ($cardsStorage->getCards() as $card) {
            $lastTransactionId = $payment->getData('cc_trans_id');
            $cardTransactionId = $card->getTransactionId();
            if ($lastTransactionId == $cardTransactionId) {
                if ($requestedAmount > 0) {
                    $prevCaptureAmount = $card->getCapturedAmount();
                    $cardAmountForCapture = $card->getProcessedAmount();
                    if ($cardAmountForCapture > $requestedAmount) {
                        $cardAmountForCapture = $requestedAmount;
                    }
                    try {
                        $newTransaction = $this->_preauthorizeCaptureCardTransaction(
                                $payment, $cardAmountForCapture, $card
                            );
                        if ($newTransaction != null) {
                            $messages[] = $newTransaction->getMessage();
                            $isSuccessful = true;
                        }
                    } catch (\Exception $e) {
                        $messages[] = $e->getMessage();
                        $isFiled = true;
                        continue;
                    }
                    $newCapturedAmount = $prevCaptureAmount + $cardAmountForCapture;
                    $card->setCapturedAmount($newCapturedAmount);
                    $cardsStorage->updateCard($card);
                    $requestedAmount = $this->_formatAmount($requestedAmount - $cardAmountForCapture);
                    if ($isSuccessful) {
                        $balance = $card->getProcessedAmount() - $card->getCapturedAmount();
                        if ($balance > 0) {
                            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_ONLY);
                            $payment->setAmount($balance);
                        }
                    }
                }
            }
        }
        if ($isFiled) {
            $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
        }
    }

    protected function _preauthorizeCaptureCardTransaction($payment, $amount, $card)
    {
        $authTransactionId = $card->getLastTransId();
        if ($payment->getCcTransId()) {
                $newTransactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
                $payment->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE);

                $payment->setAmount($amount);
                $response = $this->soapmodel
                ->prepareAuthorizeCaptureResponse($payment, $amount, false);

                if ($response->reasonCode == self::RESPONSE_CODE_SUCCESS) {
                    $captureTransactionId = $response->requestID.'-capture';
                    $card->setLastCapturedTransactionId($captureTransactionId);

                    $this->_addTransaction(
                            $payment,
                            $captureTransactionId,
                            $newTransactionType,
                            array(
                                'is_transaction_closed' => 0,
                                'parent_transaction_id' => $authTransactionId,
                            ),
                            array($this->_realTransactionIdKey => $response->requestID),
                            $this->cybersourceHelper->getTransactionMessage(
                                $payment, self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE, $response->requestID, $card, $amount
                            )
                        );
                } else {
                    $resonCode = $response->reasonCode;
                    $exceptionMessage = $this->_wrapGatewayError($this->_errorMessage[$resonCode]);
                    $exceptionMessage = $this->cybersourceHelper->getTransactionMessage(
                        $payment, self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE, $authTransactionId, $card, $amount, $exceptionMessage
                    );
                    throw new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage));
                }
            } else {
                return;
            }
    }

    protected function _formatAmount($amount, $asFloat = false)
    {
        $amount = sprintf('%.2F', $amount); // "f" depends on locale, "F" doesn't
            return $asFloat ? (float) $amount : $amount;
    }

    protected function _isGatewayActionsLocked($payment)
    {
        return $payment->getAdditionalInformation($this->_isGatewayActionsLockedKey);
    }

    protected function _generateChecksum(\Magento\Framework\DataObject $object, $checkSumDataKeys = array())
    {
        $data = array();
        foreach ($checkSumDataKeys as $dataKey) {
            $data[] = $dataKey;
            $data[] = $object->getData($dataKey);
        }

        return md5(implode($data, '_'));
    }
    protected function _processFailureMultitransactionAction($payment, $messages, $isSuccessfulTransactions)
    {
        if ($isSuccessfulTransactions) {
            $messages[] = __('Gateway actions are locked because the gateway cannot complete one or more of the transactions. Please log in to your Cybersource account to manually resolve the issue(s).');
            $currentOrderId = $payment->getOrder()->getId();
            $copyOrder = $this->ordermodel->load($currentOrderId);
            $copyOrder->getPayment()->setAdditionalInformation($this->_isGatewayActionsLockedKey, 1);
            foreach ($messages as $message) {
                $copyOrder->addStatusHistoryComment($message);
            }
            $copyOrder->save();
        }
        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(implode(' | ', $messages)));
    }

    protected function _refundCardTransaction($payment, $amount, $card)
    {
        $credit_memo = $this->registry->registry('current_creditmemo');
        $captureTransactionId = $credit_memo->getInvoice()->getTransactionId();
        if ($payment->getCcTransId()) {
                $payment->setAnetTransType(self::REQUEST_TYPE_CREDIT);
                $payment->setXTransId($payment->getTransactionId());
                $payment->setAmount($amount);
                $response = $this->soapmodel
                ->prepareRefundResponse($payment, $amount, $payment->getTransactionId());

                if ($response->reasonCode == self::RESPONSE_CODE_SUCCESS) {
                    $refundTransactionId = $response->requestID.'-refund';
                    $shouldCloseCaptureTransaction = 0;

                    if ($this->_formatAmount($card->getCapturedAmount() - $card->getRefundedAmount()) == $amount) {
                        $card->setLastTransId($refundTransactionId);
                        $shouldCloseCaptureTransaction = 1;
                    }
                    $this->_addTransaction(
                            $payment,
                            $refundTransactionId,
                            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND,
                            array(
                                'is_transaction_closed' => 1,
                                'should_close_parent_transaction' => $shouldCloseCaptureTransaction,
                                'parent_transaction_id' => $captureTransactionId,
                            ),
                            array($this->_realTransactionIdKey => $response->requestID),
                            $this->cybersourceHelper->getTransactionMessage(
                                $payment, self::REQUEST_TYPE_CREDIT, $response->requestID, $card, $amount
                            )
                        );
                } else {
                    $code = $response->reasonCode;
                    $errorMessage = $this->_errorMessage[$code];
                    $exceptionMessage = $this->cybersourceHelper->getTransactionMessage(
                        $payment, self::REQUEST_TYPE_CREDIT, $captureTransactionId, $card, $amount, $errorMessage
                    );
                    throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase($exceptionMessage));
                }
            } else {
                return;
            }
    }

    protected function _wrapGatewayError($text)
    {
        return __('Gateway error:'.$text);
    }

    private function _clearAssignedData($payment)
    {
        $payment->setCcType(null)
            ->setCcOwner(null)
            ->setCcLast4(null)
            ->setCcNumber(null)
            ->setCcCid(null)
            ->setCcExpMonth(null)
            ->setCcExpYear(null)
            ->setCcSsIssue(null)
            ->setCcSsStartMonth(null)
            ->setCcSsStartYear(null)
            ;

        return $this;
    }

    protected function _addTransaction(\Magento\Sales\Model\Order\Payment $payment, $transactionId, $transactionType,
            array $transactionDetails = array(), array $transactionAdditionalInfo = array(), $message = false
        ) 
    {
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);
        foreach ($transactionDetails as $key => $value) {
            $payment->setData($key, $value);
        }
        foreach ($transactionAdditionalInfo as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }
        $transaction = $payment->addTransaction($transactionType, null, false, $message);

        $transaction->setMessage($message);

        return $transaction;
    }

    public function processInvoice($invoice, $payment)
    {
        $lastCaptureTransId = '';
        $cardsStorage = $this->getCardsStorage($payment);
        foreach ($cardsStorage->getCards() as $card) {
            $lastTransactionId = $payment->getData('cc_trans_id');
            $cardTransactionId = $card->getTransactionId();
            if ($lastTransactionId == $cardTransactionId) {
                $lastCapId = $card->getData('last_captured_transaction_id');
                if ($lastCapId && !empty($lastCapId) && !is_null($lastCapId)) {
                    $lastCaptureTransId = $lastCapId;
                    break;
                }
            }
        }

        $invoice->setTransactionId($lastCaptureTransId);

        return $this;
    }

    public function processCreditmemo($creditmemo, $payment)
    {
        $lastRefundedTransId = '';
        $cardsStorage = $this->getCardsStorage($payment);
        foreach ($cardsStorage->getCards() as $card) {
            $lastTransactionId = $payment->getData('cc_trans_id');
            $cardTransactionId = $card->getTransactionId();
            if ($lastTransactionId == $cardTransactionId) {
                $lastCardTransId = $card->getData('last_refunded_transaction_id');
                if ($lastCardTransId && !empty($lastCardTransId) && !is_null($lastCardTransId)) {
                    $lastRefundedTransId = $lastCardTransId;
                    break;
                }
            }
        }
        $creditmemo->setTransactionId($lastRefundedTransId);

        return $this;
    }

    public function getAcceptedCurrencyCodes()
    {
        if (!$this->hasData('_accepted_currency')) {
            $acceptedCurrencyCodes = $this->_allowCurrencyCode;
            $acceptedCurrencyCodes[] = $this->getConfigModel()->getAcceptedCurrency();
            $this->setData('_accepted_currency', $acceptedCurrencyCodes);
        }

        return $this->_getData('_accepted_currency');
    }

    public function canUseForCurrency($currencyCode)
    {
        return true;  // magedelight check 2562015
            if (!in_array($currencyCode, $this->getAcceptedCurrencyCodes())) {
                return false;
            }

        return true;
    }

    public function isApplicableToQuote($quote, $checksBitMask)
    {
        if ($checksBitMask & self::CHECK_USE_FOR_COUNTRY) {
            if (!$this->canUseForCountry($quote->getBillingAddress()->getCountry())) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_FOR_CURRENCY) {
            if (!$this->canUseForCurrency($quote->getStore()->getBaseCurrencyCode())) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_CHECKOUT) {
            if (!$this->canUseCheckout()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_FOR_MULTISHIPPING) {
            if (!$this->canUseForMultishipping()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_INTERNAL) {
            if (!$this->canUseInternal()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_ORDER_TOTAL_MIN_MAX) {
            $total = $quote->getBaseGrandTotal();
            $minTotal = $this->getConfigData('min_order_total');
            $maxTotal = $this->getConfigData('max_order_total');
            if (!empty($minTotal) && $total < $minTotal || !empty($maxTotal) && $total > $maxTotal) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_ZERO_TOTAL) {
            $total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();
            if ($total < 0.0001 && $this->getCode() != 'free'
                && !($this->canManageRecurringProfiles() && $quote->hasRecurringItems())
            ) {
                return false;
            }
        }

        return true;
    }
    protected function _formatCcType($ccType)
    {
        $allTypes = $this->paymentconfig->getCcTypes();
        $allTypes = array_flip($allTypes);

        if (isset($allTypes[$ccType]) && !empty($allTypes[$ccType])) {
            return $allTypes[$ccType];
        }

        return $ccType;
    }

    protected function _processPartialAuthorizationResponse($response, $orderPayment)
    {
        if (!$response->getSplitTenderId()) {
            return false;
        }
        $quotePayment = $orderPayment->getOrder()->getQuote()->getPayment();
        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_DECLINED);
        $exceptionMessage = null;
        try {
            switch ($response->getResponseCode()) {
                    case self::RESPONSE_CODE_APPROVED:
                        $this->_registerCard($response, $orderPayment);
                        $this->_clearAssignedData($quotePayment);
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_SUCCESS);

                        return true;
                    case self::RESPONSE_CODE_HELD:
                        if ($response->getResponseReasonCode() != self::RESPONSE_REASON_CODE_PARTIAL_APPROVE) {
                            return false;
                        }
                        if ($this->getCardsStorage($orderPayment)->getCardsCount() + 1 >= self::PARTIAL_AUTH_CARDS_LIMIT) {
                            $this->cancelPartialAuthorization($orderPayment);
                            $this->_clearAssignedData($quotePayment);
                            $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_CARDS_LIMIT_EXCEEDED);
                            $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                            $exceptionMessage = __('You have reached the maximum number of credit card allowed to be used for the payment.');
                            break;
                        }
                        $orderPayment->setAdditionalInformation($this->_splitTenderIdKey, $response->getSplitTenderId());
                        $this->_registerCard($response, $orderPayment);
                        $this->_clearAssignedData($quotePayment);
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_SUCCESS);
                        $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                        $exceptionMessage = null;
                        break;
                    case self::RESPONSE_CODE_DECLINED:
                    case self::RESPONSE_CODE_ERROR:
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_DECLINED);
                        $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                        $exceptionMessage = $this->_wrapGatewayError($response->getResponseReasonText());
                        break;
                    default:
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_DECLINED);
                        $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                        $exceptionMessage = $this->_wrapGatewayError(
                            __('Payment partial authorization error.')
                        );
                }
        } catch (\Exception $e) {
            $exceptionMessage = $e->getMessage();
        }
        throw new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage));
    }
}
