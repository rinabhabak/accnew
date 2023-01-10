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
namespace Magedelight\Cybersourcesop\Controller\SilentOrder;

use Magedelight\Cybersourcesop\Gateway\Command\SilentOrder\Token\ResponseProcessCommand;
use Magedelight\Cybersourcesop\Gateway\Request\SilentOrder\MerchantSecureDataBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Payment\Block\Transparent\Iframe;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

/**
 * Class TokenResponse
 * @package Magedelight\Cybersourcesop\Controller\SilentOrder
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TokenResponse extends \Magento\Framework\App\Action\Action
{
    const TOKEN_COMMAND_NAME = 'TokenProcessCommand';

    const XML_PATH_ADMIN_URL = 'payment/cybersourcesop/adminurl';


    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect 
     */
    protected $resultRedirectFactory;


    public static $baseCardTypes = [
        'AE' => 'Amex',
        'VI' => 'Visa',
        'MC' => 'MasterCard',
        'DI' => 'Discover',
        'JBC' => 'JBC',
        'CUP' => 'China Union Pay',
        'MI' => 'Maestro',
    ];
     
    static private $ccTypeMap = [
                                    '003' => 'AE',
                                    '001' => 'VI',
                                    '002' => 'MC',
                                    '004' => 'DI',
                                    '005' => 'DN',
                                    '007' => 'JCB',
                                    '024' => 'MD',
                                    '042' => 'MI'
                                ];



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
        '480' => 'The request is marked for review by Decision Manager, Please try again after sometime',
    );

    /**
     * @param Context $context
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param LayoutFactory $layoutFactory
     * @param Registry $registry
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        Context $context,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        LayoutFactory $layoutFactory,
        Registry $registry,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CartRepositoryInterface $cartRepository,
        \Magento\Customer\Model\Session $customer,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Vault\Model\PaymentTokenFactory $paymentCardSaveTokenFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Controller\Result\Redirect $resultRedirectFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->layoutFactory = $layoutFactory;
        $this->registry = $registry;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartRepository = $cartRepository;
        $this->_customer = $customer;
        $this->_encryptor = $encryptor;
        $this->paymentCardSaveTokenFactory = $paymentCardSaveTokenFactory;
        $this->_regionFactory = $regionFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function getCustomer()
    {
        return $this->_customer->getCustomer();
    }

    protected function _getSession()
    {
        return $this->_customer;
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     * @throws \Exception
     */
    public function execute()
    {
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cybersourcetest.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $result = [];
        $arguments = [];

        /** @var Http $request */
        $request = $this->getRequest();

        $transactionType = $this->getRequest()->getParam('req_transaction_type');
        $reqmerchantsecuredata3 = $this->getRequest()->getParam('req_merchant_secure_data3');
        $response = $request->getPostValue();
        
        if (isset($reqmerchantsecuredata3) && ($reqmerchantsecuredata3 == 'frontend' || $reqmerchantsecuredata3 == 'adminhtml')) 
        {
            $response = $request->getPostValue();        

            try {
                $arguments['response'] = $request->getPostValue();
                if (!$this->getRequestField(MerchantSecureDataBuilder::MERCHANT_SECURE_DATA1)) {
                    throw new \Exception;
                }

                $activeCart = $this->cartRepository->get(
                    (int)$this->getRequestField(MerchantSecureDataBuilder::MERCHANT_SECURE_DATA1)
                );

                $payment = $this->paymentMethodManagement->get($activeCart->getId());

                 /** @var ResponseProcessCommand $command */
                $command = $this->commandPool->get(self::TOKEN_COMMAND_NAME);
                $arguments['payment'] = $this->paymentDataObjectFactory->create($payment);
                $command->execute($arguments);

                $result['success'] = true;
            } catch (\InvalidArgumentException $e) {
                throw $e;
            } catch (\Exception $e) {
                $result['error'] = true;
                $result['error_msg'] = __('Your payment has been declined. Please try again.');
            }
            $result['success'] = true;
            $this->registry->register(Iframe::REGISTRY_KEY, $result);

            $resultLayout = $this->layoutFactory->create();
            $resultLayout->addDefaultHandle();
            switch ($this->getRequestField(MerchantSecureDataBuilder::MERCHANT_SECURE_DATA3)) {
                case 'adminhtml':
                    $resultLayout
                        ->getLayout()
                        ->getUpdate()
                        ->load(['cybersourcesop_silentorder_tokenresponse_adminhtml']);
                    break;
                default:
                $logger->info('Execute from GraphQL');
                    $resultLayout
                        ->getLayout()
                        ->getUpdate()
                        ->load(['cybersourcesop_silentorder_tokenresponse']);
                    break;
            }

            return $resultLayout;
        } 
        elseif (isset($reqmerchantsecuredata3) && ($reqmerchantsecuredata3 == 'graphql')) 
        {

            $logger->info('GraphQL Starts');
            $response = $request->getPostValue();        
            try {
                $arguments['response'] = $request->getPostValue();
                if (!$this->getRequestField(MerchantSecureDataBuilder::MERCHANT_SECURE_DATA1)) {
                    throw new \Exception;
                }
                $activeCart = $this->cartRepository->get(
                    (int)$this->getRequestField(MerchantSecureDataBuilder::MERCHANT_SECURE_DATA1)
                );

                $payment = $this->paymentMethodManagement->get($activeCart->getId());

                 /** @var ResponseProcessCommand $command */
                $command = $this->commandPool->get(self::TOKEN_COMMAND_NAME);
                $arguments['payment'] = $this->paymentDataObjectFactory->create($payment);
                $command->execute($arguments);

                $result['success'] = true;
            } catch (\InvalidArgumentException $e) {
                $logger->info('error');
                $this->redirectByUrl('cybersourcesop/SilentOrder/ErrorResponse/');
            } catch (\Exception $e) {
                $logger->info('error');
                $this->redirectByUrl('cybersourcesop/SilentOrder/ErrorResponse/');
            }

            $logger->info('passed');
            $this->redirectByUrl('cybersourcesop/SilentOrder/SuccessResponse/');
        } elseif (is_string($transactionType) && $transactionType == "create_payment_token" ) 
        {
            $response = $request->getPostValue();
            $errorMessage = '';
            $customer = $this->getCustomer(); 

            $code = $this->getRequest()->getParam('reason_code');
            if ($code == '100') 
            {
                $subscriptionId = $response['payment_token'];
                $methodCode = "cybersourcesop"; 
                $year = substr($response['req_card_expiry_date'],-4);
                $month = substr($response['req_card_expiry_date'], 0, 2);
                
                $cardArray = array();
                $cardArray["type"] = $this->getCcType($response['req_card_type']);
                $cardArray["maskedCC"] = substr($response['req_card_number'], -4, 4);
                // $cardArray["expirationDate"] = $response['req_card_expiry_date'];
                $cardArray["expirationDate"] = $month."/".$year;
                
                $cardArray["firstname"] = $response['req_bill_to_forename'];
                $cardArray["lastname"] = $response['req_bill_to_surname'];
                $companyName = $this->getRequest()->getParam('req_bill_to_company_name');
                $cardArray["company"] = $companyName;
                $cardArray["street"] = $response['req_bill_to_address_line1']; 
                $cardArray["city"] = $response['req_bill_to_address_city']; 

                $stateName = $this->getRequest()->getParam('req_bill_to_address_state');
                if($response['req_bill_to_address_country']=='US' || $response['req_bill_to_address_country']=='CA')
                {
                    $region = $this->_regionFactory->create();
                    $regionId = $region->loadByCode($stateName, $response['req_bill_to_address_country'])->getId();
                    if($regionId!='')
                    {
                        $stateName = $regionId;
                    }
                }
                
                $cardArray["region_id"] = $stateName; 
                $cardArray["state"] = $stateName; 
                $cardArray["postcode"] = $response['req_bill_to_address_postal_code']; 
                $cardArray["telephone"] = $response['req_bill_to_phone']; 
                $cardArray["country_id"] = $response['req_bill_to_address_country'];
                $cardArray["payment_token"] = $response['payment_token'];
                $cardArray["request_token"] = $response['request_token'];
                $cardArray["email"] = $response['req_bill_to_email'];
                
                if (!empty($subscriptionId)) {
                    $vaultCard = [];
                    $vaultCard['gateway_token'] = $subscriptionId;
                    $vaultCard['customer_id'] = $customer->getId();
                    $vaultCard['is_active'] = true;
                    $vaultCard['is_visible'] = true;
                    $vaultCard['payment_method_code'] = $methodCode;
                    $vaultCard['type'] = 'card';
                    $expires_at = date('Y-m-d', strtotime('+1 month', strtotime($year . '-' . $month . '-01')));
                    $vaultCard['expires_at'] = $expires_at;
                    $vaultCard['details'] = json_encode($cardArray);
                    $vaultCard['public_hash'] = $this->generatePublicHash($vaultCard);
                    $CardExits =  $this->paymentCardSaveTokenFactory->create()->getCollection()->addFieldToFilter('public_hash', array("eq" => $vaultCard['public_hash']));
                    if (count($CardExits->getData()) == 0) {
                        $this->paymentCardSaveTokenFactory->create()->setData($vaultCard)->save();
                        $this->messageManager->addSuccess(__('Credit card saved successfully.'));
                        $this->redirectByUrl('vault/cards/listaction/');
                    } 
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

            $this->redirectByUrl('vault/cards/listaction/');
            
        } elseif (is_string($transactionType) && $transactionType == 'update_payment_token')
        {
            
            $errorMessage = '';
            $params = $this->getRequest()->getPostValue();
            $customer = $this->getCustomer();
            $paymentToken = $params['req_payment_token'];
            $cardDetails =  $this->paymentCardSaveTokenFactory->create()->getCollection()->addFieldToFilter('gateway_token', array("eq" => $paymentToken))->getFirstItem();
            $updateCardId = $cardDetails->getEntityId();
            if (!empty($updateCardId)) 
            {
                $cardModel =  $this->paymentCardSaveTokenFactory->create()->load($updateCardId);
                if ($cardModel->getId()) {
                    $subscriptionId = $cardModel->getData('gateway_token');
                        $code = $params['reason_code'];
                        if ($code == '100') {
                            try {
                                $newSubscriptionId = $params['req_payment_token'];
                                $model =  $this->paymentCardSaveTokenFactory->create();
                                $model->load($updateCardId);
                                $cardArray = array();
                                $methodCode = "cybersourcesop"; 
                                $reqCardNumber = $this->getRequest()->getParam('req_card_number');
                                if (empty($reqCardNumber)) 
                                {
                                    $carDetails = json_decode($model->getDetails(),true);
                                    $cardArray["type"] = $carDetails['type'];
                                    $cardArray["maskedCC"] = $carDetails['maskedCC'];
                                    $cardArray["expirationDate"] = $carDetails['expirationDate'];
                                
                                } else { 
                                    $year = substr($response['req_card_expiry_date'],-4);
                                    $month = substr($response['req_card_expiry_date'], 0, 2);
                                    $cardArray["type"] = $this->getCcType($response['req_card_type']);
                                    $cardArray["maskedCC"] = substr($response['req_card_number'], -4, 4);
                                    $cardArray["expirationDate"] = $month."/".$year;
                                }
                                $year = substr($response['req_card_expiry_date'],-4);
                                $month = substr($response['req_card_expiry_date'], 0, 2);
                                $cardArray["firstname"] = $response['req_bill_to_forename'];
                                $cardArray["lastname"] = $response['req_bill_to_surname'];
                                $companyName = $this->getRequest()->getParam('req_bill_to_company_name');
                                $cardArray["company"] = $companyName;
                                $cardArray["street"] = $response['req_bill_to_address_line1']; 
                                $cardArray["city"] = $response['req_bill_to_address_city']; 
                            $stateName = $response['req_bill_to_address_state']; 
                            if($response['req_bill_to_address_country']=='US' || $response['req_bill_to_address_country']=='CA')
                            {
                                $region = $this->_regionFactory->create();
                                $regionId = $region->loadByCode($stateName, $response['req_bill_to_address_country'])->getId();
                                if($regionId!='')
                                {
                                    $stateName = $regionId;
                                }
                            }
                            $cardArray["region_id"] = $stateName; 
                                $cardArray["state"] = $response['req_bill_to_address_state']; 
                                $cardArray["postcode"] = $response['req_bill_to_address_postal_code']; 
                                $cardArray["telephone"] = $response['req_bill_to_phone']; 
                                $cardArray["country_id"] = $response['req_bill_to_address_country'];
                                $cardArray["payment_token"] = $response['req_payment_token'];
                                $cardArray["request_token"] = $response['request_token'];
                                $cardArray["email"] = $response['req_bill_to_email'];


                                $vaultCard = [];
                                $vaultCard['gateway_token'] = $newSubscriptionId;
                                $vaultCard['customer_id'] = $customer->getId();
                                $vaultCard['is_active'] = true;
                                $vaultCard['is_visible'] = true;
                                $vaultCard['payment_method_code'] = $methodCode;
                                $vaultCard['type'] = 'card';
                                $expires_at = date('Y-m-d', strtotime('+1 month', strtotime($year . '-' . $month . '-01')));
                                $vaultCard['expires_at'] = $expires_at;
                                $vaultCard['details'] = json_encode($cardArray);
                                $vaultCard['public_hash'] = $this->generatePublicHash($vaultCard);
                                $model->setCustomerId($vaultCard['customer_id']);
                                $model->setPublicHash($vaultCard['public_hash']);
                                $model->setPaymentMethodCode($methodCode);
                                $model->setType('card');
                                $model->setExpiresAt($vaultCard['expires_at']);
                                $model->setGatewayToken($vaultCard['gateway_token']);
                                $model->setDetails($vaultCard['details']);
                                $model->save();
                            } catch (\Exception $e) {
                                $this->messageManager->addException($e, __($e->getMessage()));
                                $this->redirectByUrl('vault/cards/listaction/');
                            }
                            $this->messageManager->addSuccess(__('Card updated successfully.'));
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
                }
            }
            $this->redirectByUrl('vault/cards/listaction/');
        } else {
            $response = $request->getPostValue();  
            try {
                $arguments['response'] = $request->getPostValue();
                if (!$this->getRequestField(MerchantSecureDataBuilder::MERCHANT_SECURE_DATA1)) {
                    throw new \Exception;
                }
                $activeCart = $this->cartRepository->get(
                    (int)$this->getRequestField(MerchantSecureDataBuilder::MERCHANT_SECURE_DATA1)
                );
                $payment = $this->paymentMethodManagement->get($activeCart->getId());
                /** @var ResponseProcessCommand $command */
                $command = $this->commandPool->get(self::TOKEN_COMMAND_NAME);
                $arguments['payment'] = $this->paymentDataObjectFactory->create($payment);
                $command->execute($arguments);
                $result['success'] = true;
            } catch (\InvalidArgumentException $e) {
                throw $e;
            } catch (\Exception $e) {
                $result['error'] = true;
                $result['error_msg'] = __('Your payment has been declined. Please try again.');
            }

            $this->registry->register(Iframe::REGISTRY_KEY, $result);
            $resultLayout = $this->layoutFactory->create();
            $resultLayout->addDefaultHandle();
            switch ($this->getRequestField(MerchantSecureDataBuilder::MERCHANT_SECURE_DATA3)) {
                case 'adminhtml':
                    $resultLayout
                        ->getLayout()
                        ->getUpdate()
                        ->load(['cybersourcesop_silentorder_tokenresponse_adminhtml']);
                    break;
                default:
                    $resultLayout
                        ->getLayout()
                        ->getUpdate()
                        ->load(['cybersourcesop_silentorder_tokenresponse']);
                    break;
            }

            return $resultLayout;
        }
    }
    /**
     * Returns Cybersource-related request field
     *
     * @param string $field
     * @return mixed
     */
    private function getRequestField($field)
    {
        /** @var Http $request */
        $request = $this->getRequest();
        return $request->getPostValue($field)
            ?: $request->getPostValue('req_' . $field);
    }

    protected function generatePublicHash($vaultCard) {
        $hashKey = $vaultCard['gateway_token'];
        if ($vaultCard['customer_id']) {
            $hashKey = $vaultCard['customer_id'];
        }

        $hashKey .= $vaultCard['payment_method_code']
                . $vaultCard['type']
                . $vaultCard['details'];

        return $this->_encryptor->getHash($hashKey);
    }
    public function getCcType($cctype) {
        return self::$ccTypeMap[$cctype];
    }
    public function redirectByUrl($path)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cybersourcetest.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($this->getReirectUrlByAdminUrl().$path);
        $resultRedirect = $this->resultRedirectFactory;
        $redirectLink = $this->getReirectUrlByAdminUrl().$path; 
        $resultRedirect->setUrl($redirectLink);
        return $resultRedirect;
    }
    public function getReirectUrlByAdminUrl() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;   
        return $this->scopeConfig->getValue(self::XML_PATH_ADMIN_URL, $storeScope);
    }
}
