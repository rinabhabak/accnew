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

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const CUSTOMER_ACCOUNT_SHARE = 'customer/account_share/scope';
    const CYBERSOURCE_ACTIVE = 'payment/magedelight_cybersource/active';
    const CYBERSOURCE_TITLE = 'payment/magedelight_cybersource/title';
    const CYBERSOURCE_MERCHANT_ID = 'payment/magedelight_cybersource/merchantid';
    const CYBERSOURCE_TRANS_KEY = 'payment/magedelight_cybersource/trans_key';
    const CYBERSOURCE_TEST = 'payment/magedelight_cybersource/test';
    const CYBERSOURCE_PAYMENT_ACTION = 'payment/magedelight_cybersource/payment_action';
    const CYBERSOURCE_DEBUG = 'payment/magedelight_cybersource/debug';
    const CYBERSOURCE_CCTYPES = 'payment/magedelight_cybersource/cctypes';
    const CYBERSOURCE_CCV = 'payment/magedelight_cybersource/useccv';
    const CYBERSOURCE_SOAP_GATEWAY_URL = 'payment/magedelight_cybersource/soap_gateway_url';
    const CYBERSOURCE_SOAP_TEST_GATEWAY_URL = 'payment/magedelight_cybersource/test_soap_gateway_url';
    const CYBERSOURCE_VALIDATION_TYPE = 'payment/magedelight_cybersource/validation_mode';
    const CYBERSOURCE_CARD_SAVE_OPTIONAL = 'payment/magedelight_cybersource/save_optional';
    const CYBERSOURCE_NEW_ORDER_STATUS = 'payment/magedelight_cybersource/order_status';
    const CYBERSOURCE_ADDITIONAL_FIELD = 'payment/magedelight_cybersource/merchantdefineddata';
    const CYBERSOURCE_ADDITIONAL_FIELD1 = 'payment/magedelight_cybersource/merchantdefine_data1';
    const CYBERSOURCE_ADDITIONAL_FIELD2 = 'payment/magedelight_cybersource/merchantdefine_data2';
    const CYBERSOURCE_ADDITIONAL_FIELD3 = 'payment/magedelight_cybersource/merchantdefine_data3';
    const CYBERSOURCE_ADDITIONAL_FIELD4 = 'payment/magedelight_cybersource/merchantdefine_data4';
    const CYBERSOURCE_ADDITIONAL_FIELD5 = 'payment/magedelight_cybersource/merchantdefine_data5';
    const CYBERSOURCE_ADDITIONAL_FIELD6 = 'payment/magedelight_cybersource/merchantdefine_data6';
    const CYBERSOURCE_ADDITIONAL_FIELD7 = 'payment/magedelight_cybersource/merchantdefine_data7';

    const CYBERSOURCE_VALIDATION_NONE = 'none';
    const CYBERSOURCE_VALIDATION_TEST = 'testMode';
    const CYBERSOURCE_VALIDATION_LIVE = 'liveMode';

    protected $_storeId = null;
    protected $_backend = false;

    protected $_coreRegistry = null;

    protected $_session;

    protected $_adminsession;

    protected $scopeConfig;

    protected $encryptor;
    
    protected $customerScope;
    
    protected $sessionquote;

    protected $request;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        \Magento\Backend\Model\Session $adminsession,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Config\Share $customerScope, 
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\App\Request\Http $request,    
        array $data = []
    ) {
        $this->_storeManager        = $storeManager;
        $this->_coreRegistry        = $registry;
        $this->_session             = $quoteSession;
        $this->_adminsession        = $adminsession;
        $this->scopeConfig          = $scopeConfig;
        $this->encryptor            = $encryptor;
        $this->customerScope        = $customerScope;
        $this->request              = $request;
        $this->order                = $order;
        #$this->_backend=$this->_storeManager->getStore()->isAdmin()? true: false;
        $this->_backend = $this->checkAdmin() ? true : false;

        if ($this->_backend && $this->_coreRegistry->registry('current_order') != false) {
            $this->setStoreId($this->_coreRegistry->registry('current_order')->getStoreId());
            $this->_adminsession->setCustomerStoreId(null);
        } elseif ($this->_backend && $this->_coreRegistry->registry('current_invoice') != false) {
            $this->setStoreId($this->_coreRegistry->registry('current_invoice')->getStoreId());
            $this->_adminsession->setCustomerStoreId(null);
        } elseif ($this->_backend && $this->_coreRegistry->registry('current_creditmemo') != false) {
            $this->setStoreId($this->_coreRegistry->registry('current_creditmemo')->getStoreId());
            $this->_adminsession->setCustomerStoreId(null);
        } elseif ($this->_backend && $this->_coreRegistry->registry('current_customer') != false) {
            $this->setStoreId($this->_coreRegistry->registry('current_customer')->getStoreId());
            $this->_adminsession->setCustomerStoreId($this->_coreRegistry->registry('current_customer')->getStoreId());
        } elseif ($this->_backend && $this->_session->getStore()->getId() > 0) {
            $this->setStoreId($this->_session->getStore()->getId());
            $this->_adminsession->setCustomerStoreId(null);
        } else {
            $customerStoreSessionId = $this->_adminsession->getCustomerStoreId();
            if ($this->_backend && $customerStoreSessionId != null) {
                $this->setStoreId($customerStoreSessionId);
            } else {
                $this->setStoreId($this->_storeManager->getStore()->getId());
            }
        }
    }
    
    public function setStoreId($storeId = 0)
    {
        $this->_storeId  = $this->getStoreId();
        return $this;
    }

    public function getConfigData($field, $storeId = null)
    {
        return $this->scopeConfig->getValue($field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getIsActive()
    {
        return $this->getConfigData(self::CYBERSOURCE_ACTIVE, $this->_storeId);
    }

    /**
     * This method will return whether test mode is enabled or not.
     *
     * @return bool
     */
    public function getIsTestMode()
    {
        return $this->getConfigData(self::CYBERSOURCE_TEST, $this->_storeId);
    }

    /**
     * This metod will return CYBERSOURCE Gateway url depending on test mode enabled or not.
     *
     * @return string
     */
    public function getGatewayUrl()
    {
        $isTestMode = $this->getIsTestMode();
        $gatewayUrl = null;
        $gatewayUrl = ($isTestMode) ? $this->getConfigData(self::CYBERSOURCE_SOAP_TEST_GATEWAY_URL, $this->_storeId) : $this->getConfigData(self::CYBERSOURCE_SOAP_GATEWAY_URL, $this->_storeId);

        return $gatewayUrl;
    }

    /**
     * This methos will return Cybersource payment method title set by admin to display on onepage checkout payment step.
     *
     * @return string
     */
    public function getMethodTitle()
    {
        return (string) $this->getConfigData(self::CYBERSOURCE_TITLE, $this->_storeId);
    }

    /**
     * This method will return merchant api login id set by admin in configuration. 
     *
     * @return string
     */
    public function getMerchantId()
    {
       return $this->encryptor->decrypt($this->getConfigData(self::CYBERSOURCE_MERCHANT_ID, $this->_storeId));
    }

    /**
     * This method will return merchant api transaction key set by admin in configuration.
     *
     * @return string
     */
    public function getTransKey()
    {
        return $this->encryptor->decrypt($this->getConfigData(self::CYBERSOURCE_TRANS_KEY, $this->_storeId));
    }

    /**
     * This will returne payment action whether it is authorized or authorize and capture.
     *
     * @return string
     */
    public function getPaymentAction()
    {
        return (string) $this->getConfigData(self::CYBERSOURCE_PAYMENT_ACTION, $this->_storeId);
    }
    /**
     * This method will return whether debug is enabled from config.
     *
     * @return bool
     */
    public function getIsDebugEnabled()
    {
        return (boolean) $this->getConfigData(self::CYBERSOURCE_DEBUG, $this->_storeId);
    }

    /**
     * This method return whether card verification is enabled or not.
     *
     * @return bool
     */
    public function isCardVerificationEnabled()
    {
        return (boolean) $this->getConfigData(self::CYBERSOURCE_CCV, $this->_storeId);
    }

    /**
     * Cybersource validation mode.
     *
     * @return string
     */
    public function getValidationMode()
    {
        return (string) $this->getConfigData(self::CYBERSOURCE_VALIDATION_TYPE, $this->_storeId);
    }

    /**
     * Method which will return whether customer must save credit card as profile of not.
     *
     * @return bool
     */
    public function getSaveCardOptional()
    {
        return (boolean) $this->getConfigData(self::CYBERSOURCE_CARD_SAVE_OPTIONAL, $this->_storeId);
    }

    public function getCcTypes()
    {
        return $this->getConfigData(self::CYBERSOURCE_CCTYPES, $this->_storeId);
    }

    public function getAdditonalFieldActive()
    {
        return (boolean) $this->getConfigData(self::CYBERSOURCE_ADDITIONAL_FIELD, $this->_storeId);
    }

    public function getAdditonalField1()
    {
        return $this->getConfigData(self::CYBERSOURCE_ADDITIONAL_FIELD1, $this->_storeId);
    }

    public function getAdditonalField2()
    {
        return $this->getConfigData(self::CYBERSOURCE_ADDITIONAL_FIELD2, $this->_storeId);
    }

    public function getAdditonalField3()
    {
        return $this->getConfigData(self::CYBERSOURCE_ADDITIONAL_FIELD3, $this->_storeId);
    }

    public function getAdditonalField4()
    {
        return $this->getConfigData(self::CYBERSOURCE_ADDITIONAL_FIELD4, $this->_storeId);
    }

    public function getAdditonalField5()
    {
        return $this->getConfigData(self::CYBERSOURCE_ADDITIONAL_FIELD5, $this->_storeId);
    }

    public function getAdditonalField6()
    {
        return $this->getConfigData(self::CYBERSOURCE_ADDITIONAL_FIELD6, $this->_storeId);
    }

    public function getAdditonalField7()
    {
        return $this->getConfigData(self::CYBERSOURCE_ADDITIONAL_FIELD7, $this->_storeId);
    }

    public function getDefaultFormat()
    {
        return $this->getConfigData('customer/address_templates/html', $this->_storeId);
    }
    
    public function getCustomerAccountShare()
    {
       return $this->customerScope->isWebsiteScope();
    }
    
    public function checkAdmin()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $app_state = $om->get('Magento\Framework\App\State');
        $area_code = $app_state->getAreaCode();
        if ($app_state->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getStoreId() 
    {
        
        if($this->checkAdmin()){
           $controller =  $this->request->getControllerName(); 
           if($controller == 'order_invoice' || $controller == 'order_creditmemo')
           {
               $order_id = $this->request->getParam('order_id');
               $order = $this->order->load($order_id);
               $storeId = $order->getStoreId();
           } else {
               $storeId =  $this->_session->getQuote()->getStoreId();
           }
        } else {
            $storeId =  $this->_storeManager->getStore()->getId();
        }
        return  $storeId;  
    }
}
