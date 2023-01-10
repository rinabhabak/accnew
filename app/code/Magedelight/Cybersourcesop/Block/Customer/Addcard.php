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

class Addcard extends \Magento\Framework\View\Element\Template { 
    
    protected $_storeManager;

    protected $_configCacheType;

    protected $scopeConfig;

    protected $_customer = null;

    protected $urlBuilder;

    protected $paymentConfig;

    protected $getconfig;

    protected $directoryHelper;

    protected $_countryCollectionFactory;
    
    protected $regionCollectionFactory;


    public function __construct( 
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customer,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Directory\Block\Data $directoryBlock,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magedelight\Cybersourcesop\Gateway\Config\Config $getconfig, 
         array $data = []   
    )
    {
        $this->directoryHelper = $directoryHelper;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_configCacheType = $configCacheType;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_customer = $customer->getCustomer();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->paymentConfig = $paymentConfig;
        $this->directoryBlock = $directoryBlock;
        $this->getconfig = $getconfig;
        $this->localeResolver = $localeResolver;
        parent::__construct($context,$data);
    }
    
    public function getCountryHtmlSelect (){
        $country = $this->directoryBlock->getCountryHtmlSelect();
        return $country;
    }
    
    public function getCustomer()
    {
        return $this->_customer;
    }

    public function getBackUrl()
    {
        return $this->urlBuilder->getUrl('vault/cards/listaction/');
    }
    
    public function getSaveUrl($isTestMode)
    {
        if ($isTestMode) {
            return 'https://testsecureacceptance.cybersource.com/silent/token/create';
        }else{
            return 'https://secureacceptance.cybersource.com/silent/token/create';
        }
    }
    
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getCcAvailableTypes()
    {
        $types = $this->paymentConfig->getCcTypes();
        $availableTypes = explode(",",$this->getConfig('payment/cybersourcesop/cctypes'));
        
        if ($availableTypes) {
            foreach ($types as $code => $name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }

        return $types;
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

    public function hasVerification()
    {
        return (boolean)$this->getConfig('payment/cybersourcesop/useccv');
    }
    
    public function getPostUrl()
    {
        return $this->urlBuilder->getUrl('cybersourcesop/addcard/edit');
        //return "https://testsecureacceptance.cybersource.com/silent/token/create";
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
        //return $this->getConfig($path);
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