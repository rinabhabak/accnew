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
namespace Magedelight\Cybersourcesop\Block\Customer;

class Edit extends \Magento\Framework\View\Element\Template
{
    protected $urlBuilder;

    protected $_countryCollectionFactory;

    protected $_storeManager;

    protected $_configCacheType;

    protected $paymentConfig;

    protected $directoryHelper;

    protected $getconfig;
    
    protected $paymentCardSaveTokenFactory;
    
    protected $customerSession;
    
    protected $customerRepositoryInterface;
    
    protected $addressRepositoryInterface;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Vault\Model\PaymentTokenFactory $paymentCardSaveTokenFactory,
        \Magento\Directory\Block\Data $directoryBlock,    
        \Magedelight\Cybersourcesop\Gateway\Config\Config $getconfig, 
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,  
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface,   
        \Magento\Framework\Locale\Resolver $localeResolver,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->_storeManager = $context->getStoreManager();
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_configCacheType = $configCacheType;
        $this->paymentConfig = $paymentConfig;
        $this->directoryBlock = $directoryBlock;
        $this->paymentCardSaveTokenFactory = $paymentCardSaveTokenFactory;
        $this->directoryHelper = $directoryHelper;
        $this->customerSession = $customerSession;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->addressRepositoryInterface = $addressRepositoryInterface;
        $this->localeResolver = $localeResolver;
        $this->getconfig = $getconfig;

        parent::__construct($context,$data);
    }
    
    public function getCountryHtmlSelect ($countryid){
        $country = $this->directoryBlock->getCountryHtmlSelect($countryid);
        return $country;
    }

    public function getCard()
    {
        $cardId = $this->getRequest()->getPostValue('card_id');
        
        if (!empty($cardId)) {
            $cardData =  $this->paymentCardSaveTokenFactory->create()->load($cardId);
            return $cardData->getData();
        
        } else {
            return;
        }
    }
    
    
    public function getBackUrl()
    {
        return $this->urlBuilder->getUrl('vault/cards/listaction/');
    }

    public function getSaveUrl($isTestMode)
    {
        if ($isTestMode) {
            return 'https://testsecureacceptance.cybersource.com/silent/token/update';
        }else{
            return 'https://secureacceptance.cybersource.com/silent/token/update';
        }
    }
    
    public function getCcAvailableTypes()
    {
        $types = $this->paymentConfig->getCcTypes();
        $availableTypes = explode(',', $this->getConfig('payment/cybersourcesop/cctypes'));
          
        
        if ($availableTypes) {
            foreach ($types as $code => $name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }
        return $types;
    }
    
    public function getCustomerBillingAddress(){
        
        $customer_id =  $this->customerSession->getCustomer()->getId();
        $customer = $this->customerRepositoryInterface->getById($customer_id);
        $email = $customer->getEmail();
        $billingAddressId = $customer->getDefaultBilling();
        
        $carDetail = array();
        
        if($billingAddressId){
            $billingAddress = $this->addressRepositoryInterface->getById($billingAddressId);
            $carDetail['firstname'] = $billingAddress->getFirstName();
            $carDetail['lastname'] = $billingAddress->getLastName();
            $carDetail['company'] = $billingAddress->getCompany();
            $street = "";
            foreach($billingAddress->getStreet() as $tempStreet){
                $street .= $tempStreet;
            }
            $carDetail['street'] = $street;
            $carDetail['city'] = $billingAddress->getCity();
            $carDetail['region_id'] = $billingAddress->getRegionId();
            $carDetail['state'] = '';
            $carDetail['postcode'] = $billingAddress->getPostCode();
            $carDetail['country_id'] = $billingAddress->getCountryId();
            $carDetail['telephone'] = $billingAddress->getTelephone();
            $carDetail['email'] =  $email;
            
        } else {
            $shippingAddressId = $customer->getDefaultShipping();
            $shippingAddress = $this->addressRepositoryInterface->getById($shippingAddressId);
            
            $carDetail['firstname'] = $shippingAddress->getFirstName();
            $carDetail['lastname'] = $shippingAddress->getLastName();
            $carDetail['company'] = $shippingAddress->getCompany();
            $street = "";
            foreach($shippingAddress->getStreet() as $tempStreet){
                $street .= $tempStreet;
            }
            $carDetail['street'] = $street;
            $carDetail['city'] = $shippingAddress->getCity();
            $carDetail['region_id'] = $shippingAddress->getRegionId();
            $carDetail['state'] = '';
            $carDetail['postcode'] = $shippingAddress->getPostCode();
            $carDetail['country_id'] = $shippingAddress->getCountryId();
            $carDetail['telephone'] = $shippingAddress->getTelephone();
            $carDetail['email'] =  $email;
        }
        
        return $carDetail;
        
    }

    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] = __('Month');
            $months = array_merge($months, $this->paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }

        return $months;
    }
    public function hasVerification()
    {
        return $this->getconfig->isCardVerificationEnabled();
    }

    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (!($years)) {
            $years = $this->paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    public function getAccessKey()
    {
        return $this->getConfig('payment/cybersourcesop/access_key');
    }

    public function getProfileId()
    {
        return $this->getConfig('payment/cybersourcesop/profile_id');
    }

    public function getCurrentCurrency()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    public function getCurrentLocale()
    {
        $haystack = $this->localeResolver->getLocale();
        return strstr($haystack, '_', true); 
    }

    public function getTrancationMode()
    {
        return $this->getConfig('payment/cybersourcesop/sandbox_flag');
    }
}
