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
namespace Magedelight\Cybersource\Model\Api;

class Soap extends \Magedelight\Cybersource\Model\Api\Abstractmodel
{
    protected $_orderRequest = array();

    protected $_request = null;

    protected $_customer;

    protected $_region;

    protected $_storeManager;

    protected $_random;

    protected $request;

    protected $_soapext;

    protected $_logger;

    protected $_soaperror;

    protected $_zendlogger;

    protected $_soaplog;

    protected $_configModel;

    protected $_abstractModel;

    protected $checkoutsession;

    protected $cybersourceHelper;

    protected $regitstry;

    protected $_requestHttp;

    protected $remotaddress;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customer,
        \Magento\Directory\Model\Region $region,
        \Magedelight\Cybersource\Helper\Data $cybersourceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Math\Random $random,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remotaddress,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $requestHttp,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Magedelight\Cybersource\Model\Config $configModel,
        \Magedelight\Cybersource\Model\Api\Abstractmodel $abstractModel,
        array $data = []
    ) {
        parent::__construct($configModel);
        $this->_customer = $customer;
        $this->_region = $region;
        $this->cybersourceHelper = $cybersourceHelper;
        $this->_storeManager = $storeManager;
        $this->_random = $random;
        $this->remotaddress = $remotaddress;
        $this->registry = $registry;
        $this->_logger = $logger;
        $this->_soaperror = new \Zend\Log\Writer\Stream(BP.'/var/log/Magedelight_Cybersource_SOAPError.log');
        $this->_soaplog = new \Zend\Log\Writer\Stream(BP.'/var/log/magedelight_cybersource.log');
        $this->_zendlogger = new \Zend\Log\Logger();
        $this->_configModel = $configModel;
        $this->checkoutsession = $checkoutsession;
        $this->_abstractModel = $abstractModel;
        $this->_requestHttp = $requestHttp;
    }

    public function getCustomer()
    {
        return $this->_customer;
    }

    public function createCustomerProfile()
    {
        $request = $this->createCustomerPaymentProfileRequest();
        $response = $this->_postRequest($request);
        return $response;
    }

    public function createCustomerPaymentProfileRequest()
    {
        $inputData = $this->getInputData();
        $regionId = $inputData->getRegionId();
        $regionCode = ($regionId) ? $this->_region->load($regionId)->getCode() : $inputData->getState();
        $cardType = $this->_cardCode[$inputData->getcc_type()];
        $request = array('paySubscriptionCreateService' => array(
                'run' => 'true',
            ),
            'recurringSubscriptionInfo' => array(
                'frequency' => 'on-demand',
            ),
            'billTo' => array(
                'firstName' => $inputData->getFirstname(),
                'lastName' => $inputData->getLastname(),
                'street1' => $inputData->getStreet(),
                'city' => $inputData->getCity(),
                'state' => $regionCode,
                'postalCode' => $inputData->getPostcode(),
                'country' => $inputData->getCountryId(),
                'email' => $inputData->getEmail(),
                'customerID' => $inputData->getcustomer_id(),
            ),
            'card' => array(
                'accountNumber' => $inputData->getcc_number(),
                'expirationMonth' => $inputData->getcc_exp_month(),
                'expirationYear' => $inputData->getcc_exp_year(),
                'cardType' => $cardType,
            ),
            'purchaseTotals' => array(
                'currency' => $this->_storeManager->getStore()->getCurrentCurrencyCode(),
            ),
            'merchantID' => $this->_abstractModel->_merchantId,
            'merchantReferenceCode' => $this->_generateMerchantReferenceCode(), // magedelight have to ask 2362015
        );
        $company = $inputData->getCompany();
        if (!empty($company)) {
            $request['billTo']['company'] = $company;
        }
        if ($this->_abstractModel->_cvvEnabled) {
            $request['card']['cvNumber'] = $inputData->getcc_cid();
        }
        if ($this->_abstractModel->_additionalfield) {
            $request['merchantDefinedData']['field1'] = sprintf('Store URL : %s', $this->_getBaseUrl());
            $request['merchantDefinedData']['field2'] = sprintf('Store Name : %s', $this->_getStoreName());
        }

        return $request;
    }

    public function deleteCustomerPaymentProfile()
    {
        $request = $this->deleteCustomerPaymentProfileRequest();
        $response = $this->_postRequest($request);
        return $response;
    }

    public function deleteCustomerPaymentProfileRequest()
    {
        $inputData = $this->getInputData();
        $request = array('recurringSubscriptionInfo' => array(
                'subscriptionID' => $inputData->getCustomerSubscriptionId(),
            ),
            'paySubscriptionDeleteService' => array(
                'run' => 'true',
            ),
            'merchantID' => $this->_abstractModel->_merchantId,
            'merchantReferenceCode' => $this->_generateMerchantReferenceCode(),
        );
        if ($this->_abstractModel->_additionalfield) {
            $request['merchantDefinedData']['field1'] = sprintf('Store URL : %s', $this->_getBaseUrl());
            $request['merchantDefinedData']['field2'] = sprintf('Store Name : %s', $this->_getStoreName());
        }
        return $request;
    }

    public function updateCustomerProfile()
    {
        $request = $this->updateCustomerProfileRequest();
        $response = $this->_postRequest($request);
        return $response;
    }
    public function updateCustomerProfileRequest()
    {
        $inputData = $this->getInputData();
        $regionId = $inputData->getRegionId();
        $regionCode = ($regionId) ? $this->_region->load($regionId)->getCode() : $inputData->getState();
        if (isset($this->_cardCode[$inputData->getcc_type()])) {
            $cardType = $this->_cardCode[$inputData->getcc_type()];  // magedelight to get card id from card code
        }
        $cardUpdateCheck = $inputData->getcc_action();
        $request = array('recurringSubscriptionInfo' => array(
                    'subscriptionID' => $inputData->getCustomerSubscriptionId(),
                ),
                'paySubscriptionUpdateService' => array(
                    'run' => 'true',
                ),
                'billTo' => array(
                    'firstName' => $inputData->getFirstname(),
                    'lastName' => $inputData->getLastname(),
                    'street1' => $inputData->getStreet(),
                    'city' => $inputData->getCity(),
                    'state' => $regionCode,
                    'postalCode' => $inputData->getPostcode(),
                    'country' => $inputData->getCountryId(),
                    'email' => $inputData->getEmail(),
                    'customerID' => $inputData->getcustomer_id(),
                ),
                'merchantID' => $this->_abstractModel->_merchantId,
                'merchantReferenceCode' => $this->_generateMerchantReferenceCode(), // magedelight have to ask 2362015
            );
        if ($cardUpdateCheck != 'existing') {
            $request['card']['accountNumber'] = $inputData->getcc_number();
            $request['card']['expirationMonth'] = $inputData->getcc_exp_month();
            $request['card']['expirationYear'] = $inputData->getcc_exp_year();
            $request['card']['cardType'] = $cardType;
            if ($this->_abstractModel->_cvvEnabled) {
                $request['card']['cvNumber'] = $inputData->getcc_cid();
            }
        }
        $company = $inputData->getCompany();
        if (!empty($company)) {
            $request['billTo']['company'] = $company;
        }
        if ($this->_abstractModel->_additionalfield) {
            $request['merchantDefinedData']['field1'] = sprintf('Store URL : %s', $this->_getBaseUrl());
            $request['merchantDefinedData']['field2'] = sprintf('Store Name : %s', $this->_getStoreName());
        }

        return $request;
    }

    public function createCustomerProfileFromTransaction($requestid)
    {
        $this->_request = new \stdClass();
        if (!empty($requestid)) {
            $this->_request->merchantID = $this->_abstractModel->_merchantId;
            $this->_request->merchantReferenceCode = $this->_generateMerchantReferenceCode();
            $paySubscriptionCreateService = new \stdClass();
            $paySubscriptionCreateService->run = 'true';
            $paySubscriptionCreateService->paymentRequestID = $requestid;
            $this->_request->paySubscriptionCreateService = $paySubscriptionCreateService;
            $recurringSubscriptionInfo = new \stdClass();
            $recurringSubscriptionInfo->frequency = 'on-demand';
            $this->_request->recurringSubscriptionInfo = $recurringSubscriptionInfo;
            $response = $this->_postRequest($this->_request);
            return $response;
        }
    }

    public function prepareCaptureResponse(\Magento\Payment\Model\InfoInterface $payment, $amount, $subscription = false)
    {
        $this->_request = $this->prepareCaptureRequest($payment, $amount, $subscription);
        $response = $this->_postRequest($this->_request);

        return $response;
    }
    public function prepareCaptureRequest(\Magento\Payment\Model\InfoInterface $payment, $amount, $subscription)
    {
        $this->_request = new \stdClass();
        $billingAddress = $payment->getOrder()->getBillingAddress();
        $shippingAddress = $payment->getOrder()->getShippingAddress();
        $this->_request->merchantID = $this->_abstractModel->_merchantId;
        $this->_request->merchantReferenceCode = $this->_generateMerchantReferenceCode();
        $ccAuthService = new \stdClass();
        $ccAuthService->run = 'true';
        $this->_request->ccAuthService = $ccAuthService;

        $ccCaptureService = new \stdClass();
        $ccCaptureService->run = 'true';
        $this->_request->ccCaptureService = $ccCaptureService;

        $customeremail = $payment->getOrder()->getCustomerEmail();
        if (!$customeremail) {
            $customeremail = $this->checkoutsession->getQuote()->getBillingAddress()->getEmail();
        }

        $this->createBillingAddressRequest($customeremail, $billingAddress);

        $this->createShippingAddressRequest($shippingAddress);

        if ($subscription == false) {
            $this->createCardInfoRequest($payment);
        } else {
            $subscription_info = new \stdClass();         // magedelight here we set subscerption id insted of card info
                $subscription_info->subscriptionID = $payment->getMagedelightcybersourceSubscriptionId();
            $this->_request->recurringSubscriptionInfo = $subscription_info;
            if ($this->_abstractModel->_cvvEnabled) {
                $card = new \stdClass();
                $card->cvNumber = $payment->getcc_cid();
                $this->_request->card = $card;
            }
        }

        $this->createItemInfoRequest($payment);

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = $amount;
        if ($payment->getBaseShippingAmount()) {
            $purchaseTotals->additionalAmount0 = (string) round($payment->getBaseShippingAmount(), 4);
            $purchaseTotals->additionalAmountType0 = (string) '055';
        }

        $this->_request->purchaseTotals = $purchaseTotals;

        if ($this->_abstractModel->_additionalfield) {
            $this->getAdditionalData($payment);
        }

        return  $this->_request;
    }

    protected function createItemInfoRequest(\Magento\Payment\Model\InfoInterface $payment, $quantity = false)
    {
        if (is_object($payment)) {
            $order = $payment->getOrder();
            if ($order instanceof \Magento\Sales\Model\Order) {
                $i = 0;
                foreach ($order->getAllVisibleItems() as $_item) {
                    $item = new \stdClass();
                    $item->unitPrice = round($_item->getBasePrice(), 2);
                    $item->taxAmount = round($_item->getData('tax_amount'), 2);
                    $quantity == false ? $item->quantity = (int) $_item->getQtyOrdered() : '';
                    $item->productName = substr($_item->getName(), 0, 30);
                    $item->productSKU = $_item->getSku();
                    $item->id = $i;
                    $this->_request->item[$i] = $item;
                    ++$i;
                }
            }
        }
    }

    protected function createBillingAddressRequest($customeremail, $billing)
    {
        $customerId = false;
        if (!$customeremail) {
            $customeremail = $this->checkoutsession->getQuote()->getBillingAddress()->getEmail();
        }
        if ($this->_customer->isLoggedIn()) {
            $customerData = $this->_customer->getCustomer();
            $customerId = $customerData->getId();
        }
        $billTo = new \stdClass();
        $billTo->firstName = $billing->getFirstname();
        $billTo->lastName = $billing->getLastname();
        $billTo->company = $billing->getCompany();
        $billTo->street1 = $billing->getStreet()[0];
        $billTo->city = $billing->getCity();
        $billTo->state = $billing->getRegion();
        $billTo->postalCode = $billing->getPostcode();
        $billTo->country = $billing->getCountryId();
        $billTo->phoneNumber = $billing->getTelephone();
        $billTo->email = $customeremail;
        $billTo->ipAddress = $this->getIpAddress();

        if ($customerId) {
            $billTo->customerID = $customerId;
        }
        $this->_request->billTo = $billTo;
    }

    protected function createShippingAddressRequest($shipping)
    {
        if ($shipping) {
            $shipTo = new \stdClass();
            $shipTo->firstName = $shipping->getFirstname();
            $shipTo->lastName = $shipping->getLastname();
            $shipTo->company = $shipping->getCompany();
            $shipTo->street1 = $shipping->getStreet()[0];
            $shipTo->city = $shipping->getCity();
            $shipTo->state = $shipping->getRegion();
            $shipTo->postalCode = $shipping->getPostcode();
            $shipTo->country = $shipping->getCountryId();
            $shipTo->phoneNumber = $shipping->getTelephone();
            $this->_request->shipTo = $shipTo;
        }
    }

    protected function createCardInfoRequest($payment)
    {
        if (is_object($payment)) {
            $post = $this->_requestHttp->getParam('payment');
            $ccNumber = $payment->getCcNumber();
            $expMonth = $payment->getCcExpMonth();
            $expYear = $payment->getCcExpYear();
            $ccType = $payment->getcc_type();
            $cardType = '';
            if (isset($this->_cardCode[empty($ccType) ? $post['cc_type'] : $ccType])) {
                $cardType = $this->_cardCode[empty($ccType) ? $post['cc_type'] : $ccType];  // magedelight to get card id from card code
            }

            $card = new \stdClass();
            $card->accountNumber = empty($ccNumber) ? $post['cc_number'] : $ccNumber;
            $card->expirationMonth = empty($expMonth) ? $post['cc_exp_month'] : $expMonth;
            $card->expirationYear = empty($expYear) ? $post['cc_exp_year'] : $expYear;
            $card->cardType = $cardType;
            if ($this->_abstractModel->_cvvEnabled) {
                $ccId = $payment->getcc_cid();
                $card->cvNumber = empty($ccId) ? $post['cc_cid'] : $ccId;
            }
            $this->_request->card = $card;
        }
    }

    protected function getAdditionalData(\Magento\Payment\Model\InfoInterface $payment)
    {
        if (is_object($payment)) {
               $additonalData = array(
                    'store_url' => $this->_storeManager->getStore()->getBaseUrl(),
                    'store_name' => strip_tags($payment->getOrder()->getStoreName()),
                    'order_id' => $payment->getOrder()->getIncrementId(),
                    'shipping_amount' => round($payment->getBaseShippingAmount(), 4),
                    'shipping_name' => $payment->getOrder()->getShippingDescription(),
                    'discount' => $payment->getOrder()->getDiscountAmount(),
                    'coupon' => $payment->getOrder()->getCouponCode(),
                );

            $requiredAdditionalData = array(
                    $this->_abstractModel->_additionalfield1,
                    $this->_abstractModel->_additionalfield2,
                    $this->_abstractModel->_additionalfield3,
                    $this->_abstractModel->_additionalfield4,
                    $this->_abstractModel->_additionalfield5,
                    $this->_abstractModel->_additionalfield6,
                    $this->_abstractModel->_additionalfield7,
                );

            $additonalDataLabel = array(
                    'store_url' => __('Store URL :'),
                    'store_name' => __('Store Name :'),
                    'order_id' => __('Order ID # :'),
                    'shipping_amount' => __('Shipping Amount :'),
                    'shipping_name' => __('Shipping Method Name :'),
                    'discount' => __('Discount Amount :'),
                    'coupon' => __('Coupon Code :'),
                );
            $merchantDefinedata = new \stdClass();
            $count = 0;
            $requiredAdditionalData = array_values(array_filter($requiredAdditionalData));
            for ($t = 1;$t <= count($requiredAdditionalData);++$t) {
                if ((!empty($additonalData[$requiredAdditionalData[$count]])
                            && $additonalData[$requiredAdditionalData[$count]] != '')
                            || $requiredAdditionalData[$count] == 'shipping_amount') {
                    $merchantDefinedata->{'field'.$t} = $additonalDataLabel[$requiredAdditionalData[$count]].' '.$additonalData[$requiredAdditionalData[$count]];
                }
                ++$count;
            }
            $this->_request->merchantDefinedData = $merchantDefinedata;
        }
    }

    public function prepareAuthorizeResponse(\Magento\Payment\Model\InfoInterface $payment, $amount, $subscription = false)
    {
        $this->_request = $this->prepareAuthorizeRequest($payment, $amount, $subscription);
        $response = $this->_postRequest($this->_request);
        return $response;
    }

    public function prepareAuthorizeRequest(\Magento\Payment\Model\InfoInterface $payment, $amount, $subscription)
    {
        $this->_request = new \stdClass();
        $billingAddress = $payment->getOrder()->getBillingAddress();
        $shippingAddress = $payment->getOrder()->getShippingAddress();
        $this->_request->merchantID = $this->_abstractModel->_merchantId;
        $this->_request->merchantReferenceCode = $this->_generateMerchantReferenceCode();
        $ccAuthService = new \stdClass();
        $ccAuthService->run = 'true';
        $this->_request->ccAuthService = $ccAuthService;

        $customeremail = $payment->getOrder()->getCustomerEmail();
        if (!$customeremail) {
            $customeremail = $this->checkoutsession->getQuote()->getBillingAddress()->getEmail();
        }
        $this->createBillingAddressRequest($customeremail, $billingAddress);

        $this->createShippingAddressRequest($shippingAddress);

        if ($subscription == false) {
            $this->createCardInfoRequest($payment);
        } else {
            $subscription_info = new \stdClass();         // magedelight here we set subscerption id insted of card info
                $subscription_info->subscriptionID = $payment->getMagedelightcybersourceSubscriptionId();
            $this->_request->recurringSubscriptionInfo = $subscription_info;
            if ($this->_abstractModel->_cvvEnabled) {
                $card = new \stdClass();
                $card->cvNumber = $payment->getcc_cid();
                $this->_request->card = $card;
            }
        }

        $this->createItemInfoRequest($payment);

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = $amount;
        if ($payment->getBaseShippingAmount()) {
            $purchaseTotals->additionalAmount0 = (string) round($payment->getBaseShippingAmount(), 4);
            $purchaseTotals->additionalAmountType0 = (string) '055';
        }

        $this->_request->purchaseTotals = $purchaseTotals;

        if ($this->_abstractModel->_additionalfield) {
            $this->getAdditionalData($payment);
        }

        return  $this->_request;
    }

    public function prepareAuthorizeCaptureResponse(\Magento\Payment\Model\InfoInterface $payment, $amount, $subscription = false)
    {
        $this->_request = $this->prepareAuthorizeCaptureRequest($payment, $amount, $subscription);
        $response = $this->_postRequest($this->_request);

        return $response;
    }

    public function prepareAuthorizeCaptureRequest(\Magento\Payment\Model\InfoInterface $payment, $amount, $subscription)
    {
        $this->_request = new \stdClass();
        $billingAddress = $payment->getOrder()->getBillingAddress();
        $shippingAddress = $payment->getOrder()->getShippingAddress();

        $this->_request->merchantID = $this->_abstractModel->_merchantId;
        $this->_request->merchantReferenceCode = $this->_generateMerchantReferenceCode();
        $csCaptureService = new \stdClass();
        $csCaptureService->run = 'true';

        $csCaptureService->authRequestToken = $payment->getCybersourceToken();
        $csCaptureService->authRequestID = $payment->getmagedelightCybersourceRequestid();

        $this->_request->ccCaptureService = $csCaptureService;
        $item0 = new \stdClass();
        $item0->unitPrice = $amount;
        $item0->id = 0;
        $this->_request->item = array($item0);

        $customeremail = $payment->getOrder()->getCustomerEmail();

        $this->createBillingAddressRequest($customeremail, $billingAddress);
        $this->createShippingAddressRequest($shippingAddress);

        $this->createItemInfoRequest($payment);

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = $amount;
        if ($payment->getBaseShippingAmount()) {
            $purchaseTotals->additionalAmount0 = (string) round($payment->getBaseShippingAmount(), 4);
            $purchaseTotals->additionalAmountType0 = (string) '055';
        }

        $this->_request->purchaseTotals = $purchaseTotals;

        if ($this->_abstractModel->_additionalfield) {
            $this->getAdditionalData($payment);
        }

        return  $this->_request;
    }

    public function prepareVoidResponse(\Magento\Payment\Model\InfoInterface $payment, $card)
    {
        $this->_request = new \stdClass();
        $billingAddress = $payment->getOrder()->getBillingAddress();
        $this->_request->merchantID = $this->_abstractModel->_merchantId;
        $this->_request->merchantReferenceCode = $this->_generateMerchantReferenceCode();

        $ccAuthReversalService = new \stdClass();
        $ccAuthReversalService->run = 'true';
        $ccAuthReversalService->authRequestID = (string) $payment->getParentTransactionId();
        $ccAuthReversalService->authRequestToken = (string) $payment->getCybersourceToken();
        $this->_request->ccAuthReversalService = $ccAuthReversalService;

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = $payment->getBaseAmountAuthorized();
        $this->_request->purchaseTotals = $purchaseTotals;

        $customeremail = $payment->getOrder()->getCustomerEmail();

        $this->createBillingAddressRequest($customeremail, $billingAddress);
        if ($this->_abstractModel->_additionalfield) {
                $this->getAdditionalData($payment);
            }

        $response = $this->_postRequest($this->_request);

        return $response;
    }

    protected function prepareRefundRequest(\Magento\Payment\Model\InfoInterface $payment, $amount, $realCaptureTransactionId)
    {
        $this->_request = new \stdClass();
        $billingAddress = $payment->getOrder()->getBillingAddress();
        $shippingAddress = $payment->getOrder()->getShippingAddress();

        $this->_request->merchantID = $this->_abstractModel->_merchantId;
        $this->_request->merchantReferenceCode = $this->_generateMerchantReferenceCode();
        $ccCreditService = new \stdClass();
        $ccCreditService->run = (string) 'true';
        $ccCreditService->captureRequestID = (string) $realCaptureTransactionId;
        $ccCreditService->captureRequestToken = (string) $payment->getCybersourceToken();
        $this->_request->ccCreditService = $ccCreditService;

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = $amount;
        $this->_request->purchaseTotals = $purchaseTotals;

        $customeremail = $payment->getOrder()->getCustomerEmail();

        $this->createBillingAddressRequest($customeremail, $billingAddress);

        $this->createShippingAddressRequest($shippingAddress);

        $this->createItemInfoRequest($payment);

        if ($this->_abstractModel->_additionalfield) {
            $this->getAdditionalData($payment);
        }

        return  $this->_request;
    }
    protected function getIpAddress()
    {
        return $this->remotaddress->getRemoteAddress();
    }
    public function prepareRefundResponse(\Magento\Payment\Model\InfoInterface $payment, $amount, $realCaptureTransactionId)
    {
        $this->_request = $this->prepareRefundRequest($payment, $amount, $realCaptureTransactionId);
        $response = $this->_postRequest($this->_request);

        return $response;
    }
    protected function _generateMerchantReferenceCode()
    {
        return $this->_random->getUniqueHash();
    }

    protected function _getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }

    protected function _getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }

    public function _postRequest($request, $type = null)
    {
        $debug = array();

        $client = new \Magedelight\Cybersource\Model\Api\SoapClient($this->_configModel); // magedelight for ws security we have to override soap  because we have to add username token in soap request         
        try {
           $response = $client->runTransaction($request);
        } catch (\SoapFault $sf) {
            $logger = $this->_zendlogger;
            $logger->addWriter($this->_soaperror);
            $logger->info("Cybersource SOAP Request Error  : => $sf");
            throw new \Magento\Framework\Exception\LocalizedException(__('Soap request error due to invalid configuration.'), $sf);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $logger = $this->_zendlogger;
            $logger->addWriter($this->_soaperror);
            $logger->info("Cybersource Exception Error Due to  : => $message");
            throw new \Magento\Framework\Exception\LocalizedException(__($message), $e);
        }

        if ($this->getConfigModel()->getIsDebugEnabled()) {
            $this->prepareSoapForDebug($request, $response);
        }

        return $response;
    }

    public function prepareSoapForDebug($request, $response)
    {
        $requstArray = json_decode(json_encode($request), true);
        $responseArr = json_decode(json_encode($response), true);
        $responseArray = $this->createValidArray($responseArr);
        $cybersourceRequest = new \SimpleXMLElement('<?xml version="1.0"?><cybersource_request_info></cybersource_request_info>');
        $this->array_to_xml($requstArray, $cybersourceRequest);
        $cybersourceRequestXMLFile = $cybersourceRequest->asXML();
        $dom = new \DOMDocument();
        $dom->loadXML($cybersourceRequestXMLFile);
        $dom->formatOutput = true;
        $RequestXML = '';
        $ResponseXML = '';
        $RequestXML .= "Request:\n\n";
        $RequestXML .= $dom->saveXML();
        /*print request log */
        $logger = $this->_zendlogger;
        $logger->addWriter($this->_soaplog);
        $logger->info("$RequestXML");
        $cybersourceResponse = new \SimpleXMLElement('<?xml version="1.0"?><cybersource_response_info></cybersource_response_info>');
        
        $this->array_to_xml($responseArray, $cybersourceResponse);
        $cybersourceResponseXMLFile = $cybersourceResponse->asXML();
        $dom = new \DOMDocument();
        $dom->loadXML($cybersourceResponseXMLFile);
        $dom->formatOutput = true;
        $ResponseXML .= "Response:\n\n";
        $ResponseXML .= $dom->saveXML();
        /*print response log*/
        $logger = $this->_zendlogger;
        $logger->addWriter($this->_soaplog);
        $logger->info("$ResponseXML");
    }
    public function createValidArray($response){
        if(isset($response['invalidField'])){
            if(is_array($response['invalidField'])){
                foreach ($response as $reskey => $resvalue) {
                    if($reskey=='invalidField'){
                        foreach ($response[$reskey] as $invkey => $invalue) {
                            $response[$reskey]["res" . $invkey] = $response[$reskey][$invkey];
                            unset($response[$reskey][$invkey]);
                        }
                    }
                }
                return $response;
            }
            else{
                return $response;
            }
        }
        return $response;
    }

    public function array_to_xml($array, &$xml_user_info)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xml_user_info->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                } else {
                    $subnode = $xml_user_info->addChild("item$key");
                    $this->array_to_xml($value, $subnode);
                }
            } else {
                if ($key == 'accountNumber') {      // magedelight 672015 for security reasion we cant log sensitive inbfromation so we have put  XXXX
                    $value = substr($value, -4, 4);
                    $value = 'XXXX-'.$value;
                }
                if ($key == 'expirationMonth') {
                    $value = 'XX';
                }
                if ($key == 'expirationYear') {
                    $value = 'XXXX';
                }
                if ($key == 'cvNumber') {
                    $value = 'XX';
                }
                if ($key == 'cardType') {
                    $value = 'XX';
                }
                if ($key == 'merchantID') {
                    $value = 'XXXX';
                }
                $xml_user_info->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}
