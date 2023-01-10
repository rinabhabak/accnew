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
* @package Magedelight_Cybersourcesop
* @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
*/
namespace Magedelight\Cybersourcesop\Gateway\Config;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const CYBERSOURCE_ACTIVE = 'active';
    const CYBERSOURCE_VAULT_ACTIVE = 'active';
    const CYBERSOURCE_TITLE = 'title';
    const CYBERSOURCE_MERCHANT_ID = 'merchant_id';
    const CYBERSOURCE_TRANS_KEY = 'transaction_key';
    const CYBERSOURCE_TEST = 'sandbox_flag';
    const CYBERSOURCE_PAYMENT_ACTION = 'payment_action';
    const CYBERSOURCE_DEBUG = 'debug';
    const CYBERSOURCE_CCTYPES = 'cctypes';
    const CYBERSOURCE_CCV = 'useccv';
    const CYBERSOURCE_SOAP_GATEWAY_URL = 'soap_gateway_url';
    const CYBERSOURCE_SOAP_TEST_GATEWAY_URL = 'test_soap_gateway_url';
    const CYBERSOURCE_VALIDATION_TYPE = 'validation_mode';
    const CYBERSOURCE_CARD_SAVE_OPTIONAL = 'save_optional';
    const CYBERSOURCE_NEW_ORDER_STATUS = 'order_status';
    const CYBERSOURCE_ADDITIONAL_FIELD = 'merchantdefineddata';
    const CYBERSOURCE_ADDITIONAL_FIELD1 = 'merchantdefine_data1';
    const CYBERSOURCE_ADDITIONAL_FIELD2 = 'merchantdefine_data2';
    const CYBERSOURCE_ADDITIONAL_FIELD3 = 'merchantdefine_data3';
    const CYBERSOURCE_ADDITIONAL_FIELD4 = 'merchantdefine_data4';
    const CYBERSOURCE_ADDITIONAL_FIELD5 = 'merchantdefine_data5';
    const CYBERSOURCE_ADDITIONAL_FIELD6 = 'merchantdefine_data6';
    const CYBERSOURCE_ADDITIONAL_FIELD7 = 'merchantdefine_data7';
    const CYBERSOURCE_HOSTHEDGE = 'host_hedge';
    const CYBERSOURCE_NONSENSICALHEDGE = 'nonsensical_hedge';
    const CYBERSOURCE_OBSCENITIESHEDGE = 'obscenities_hedge';
    const CYBERSOURCE_PHONEHEDGE = 'phone_hedge';
    const CYBERSOURCE_TIMEHEDGE = 'time_hedge';
    const CYBERSOURCE_VELOCITYHEDGE = 'velocity_hedge';
    const CYBERSOURCE_GIFTCATEGORY = 'giftcategory_hedge';
    const CYBERSOURCE_CGI_URL_TEST_MODE = 'cgi_url_test_mode';
    const CYBERSOURCE_CGI_URL = 'cgi_url';


    const CYBERSOURCE_VALIDATION_NONE = 'none';
    const CYBERSOURCE_VALIDATION_TEST = 'testMode';
    const CYBERSOURCE_VALIDATION_LIVE = 'liveMode';
    
    public function getIsActive()
    {
        return (boolean)$this->getValue(self::CYBERSOURCE_ACTIVE);
    }

    public function getIsVaultActive()
    {
        return (boolean)$this->getValue(self::CYBERSOURCE_VAULT_ACTIVE);
    }

    /**
     * This method will return whether test mode is enabled or not.
     *
     * @return bool
     */
    public function getIsTestMode()
    {
         return (boolean)$this->getValue(self::CYBERSOURCE_TEST);
    }

     /**
     * This metod will return CYBERSOURCE Gateway url depending on test mode enabled or not.
     *
     * @return string
     */
    public function getGatewayUrl()
    {
        $isTestMode = $this->getIsTestMode();
        $gatewayUrl = ($isTestMode) ? $this->getValue(self::CYBERSOURCE_SOAP_TEST_GATEWAY_URL) :
            $this->getValue(self::CYBERSOURCE_SOAP_GATEWAY_URL);
        return $gatewayUrl;
    }

    /**
     * This methos will return Cybersource payment method title set by admin to display
     * on onepage checkout payment step.
     *
     * @return string
     */
    public function getMethodTitle()
    {
        return (string) $this->getValue(self::CYBERSOURCE_TITLE);
    }

    /**
     * This method will return merchant api login id set by admin in configuration.
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->getValue(self::CYBERSOURCE_MERCHANT_ID);
    }
    /**
     * This method will return merchant api transaction key set by admin in configuration.
     *
     * @return string
     */
    public function getTransKey()
    {
        return $this->getValue(self::CYBERSOURCE_TRANS_KEY);
    }

    /**
     * This will returne payment action whether it is authorized or authorize and capture.
     *
     * @return string
     */
    public function getPaymentAction()
    {
        return (string) $this->getValue(self::CYBERSOURCE_PAYMENT_ACTION);
    }
    /**
     * This method will return whether debug is enabled from config.
     *
     * @return bool
     */
    public function getIsDebugEnabled()
    {
        return (boolean) $this->getValue(self::CYBERSOURCE_DEBUG);
    }

    /**
     * This method return whether card verification is enabled or not.
     *
     * @return bool
     */
    public function isCardVerificationEnabled()
    {
        return (boolean) $this->getValue(self::CYBERSOURCE_CCV);
    }

    /**
     * Cybersource validation mode.
     *
     * @return string
     */
    public function getValidationMode()
    {
        return (string) $this->getValue(self::CYBERSOURCE_VALIDATION_TYPE);
    }

    public function getCcTypes()
    {
        $ccTypes =  $this->getValue(self::CYBERSOURCE_CCTYPES);
        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    public function getAdditonalFieldActive()
    {
        return (boolean) $this->getValue(self::CYBERSOURCE_ADDITIONAL_FIELD);
    }

    public function getAdditonalField1()
    {
        return $this->getValue(self::CYBERSOURCE_ADDITIONAL_FIELD1);
    }

    public function getAdditonalField2()
    {
        return $this->getValue(self::CYBERSOURCE_ADDITIONAL_FIELD2);
    }

    public function getAdditonalField3()
    {
        return $this->getValue(self::CYBERSOURCE_ADDITIONAL_FIELD3);
    }

    public function getAdditonalField4()
    {
        return $this->getValue(self::CYBERSOURCE_ADDITIONAL_FIELD4);
    }

    public function getAdditonalField5()
    {
        return $this->getValue(self::CYBERSOURCE_ADDITIONAL_FIELD5);
    }

    public function getAdditonalField6()
    {
        return $this->getValue(self::CYBERSOURCE_ADDITIONAL_FIELD6);
    }

    public function getAdditonalField7()
    {
        return $this->getValue(self::CYBERSOURCE_ADDITIONAL_FIELD7);
    }
    public function getHostHedge()
    {
        return $this->getValue(self::CYBERSOURCE_HOSTHEDGE);
    }
    public function getNonsensicalHedge()
    {
        return $this->getValue(self::CYBERSOURCE_NONSENSICALHEDGE);
    }
    public function getObscenitiesHedge()
    {
        return $this->getValue(self::CYBERSOURCE_OBSCENITIESHEDGE);
    }
    public function getPhoneHedge()
    {
        return $this->getValue(self::CYBERSOURCE_PHONEHEDGE);
    }
    public function getTimeHedge()
    {
        return $this->getValue(self::CYBERSOURCE_TIMEHEDGE);
    }
    public function getVelocityHedge()
    {
        return $this->getValue(self::CYBERSOURCE_VELOCITYHEDGE);
    }
    public function getGiftCategory()
    {
        return $this->getValue(self::CYBERSOURCE_GIFTCATEGORY);
    }

    public function getCgiUrlTestMode()
    {
        return $this->getValue(self::CYBERSOURCE_CGI_URL_TEST_MODE);
    }

    public function getCgiUrl()
    {
        return $this->getValue(self::CYBERSOURCE_CGI_URL);
    }
    
    public function getDefaultFormat()
    {
        return $this->scopeConfig->getValue('customer/address_templates/html',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}