<?php
namespace Int\CyberSource\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */    
    protected $_scopeConfig;
    
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    
    /*
     * @var \CyberSource\Authentication\Core\MerchantConfiguration
    **/
    protected $_cyberSourceMerchantConfiguration;
    
    /*
     * @var \CyberSource\Authentication\Core\MerchantConfiguration
    **/
    protected $_cyberSourceLogConfiguration;
    
    /*
     * @var \CyberSource\Configuration
    **/
    protected $_cyberSourceConfiguration;

    protected $authType;
    protected $merchantID;
    protected $apiKeyID;
    protected $secretKey;

    // MetaKey configuration [Start]
    protected $useMetaKey;
    protected $portfolioID;
    // MetaKey configuration [End]

    protected $keyAlias;
    protected $keyPass;
    protected $keyFilename;
    protected $keyDirectory;
    protected $runEnv;

    //OAuth related config
    protected $enableClientCert;
    protected $clientCertDirectory;
    protected $clientCertFile;
    protected $clientCertPassword;
    protected $clientId;
    protected $clientSecret;

    const REST_LIVE = 'api.cybersource.com';
    const REST_TEST = 'apitest.cybersource.com';
   
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Dompdf\Dompdf
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \CyberSource\Authentication\Core\MerchantConfiguration $cyberSourceMerchantConfiguration,
        \CyberSource\Logging\LogConfiguration $cyberSourceLogConfiguration,
        \CyberSource\Configuration $cyberSourceConfiguration
        
    ) {
        $this->_scopeConfig     = $scopeConfig;
        $this->_storeManager    = $storeManager;
        $this->_cyberSourceMerchantConfiguration = $cyberSourceMerchantConfiguration;
        $this->_cyberSourceLogConfiguration = $cyberSourceLogConfiguration;
        $this->_cyberSourceConfiguration = $cyberSourceConfiguration;
        
      
        $this->cyberSourceParams();
        $this->merchantConfigObject();
        
        parent::__construct($context);
    }


    public function cyberSourceParams()
    {
        $this->authType = "http_signature";
        $this->merchantID = $this->getConfig('payment/int_cybersource/merchant_id');      // wfgaccurideint
        $this->apiKeyID   = $this->getConfig('payment/int_cybersource/api_key');          // 99fab8b9-809b-4bf4-a210-a9e93a405b51
        $this->secretKey  = $this->getConfig('payment/int_cybersource/secret_key');       // F7r4vW7nfOgFv7DXt2drkeNePQxb+4oOLqx9kEicfCM=

        // MetaKey configuration [Start]
        $this->useMetaKey = false;
        $this->portfolioID = "";
        // MetaKey configuration [End]

        $this->keyAlias     = $this->getConfig('payment/int_cybersource/merchant_id');
        $this->keyPass      = $this->getConfig('payment/int_cybersource/merchant_id');
        $this->keyFilename  = $this->getConfig('payment/int_cybersource/merchant_id');
        $this->keyDirectory = "Resources/";

        if($this->getConfig('payment/int_cybersource/test')):
            $this->runEnv =  static::REST_TEST; //apitest.cybersource.com
        else:
            $this->runEnv =  static::REST_LIVE; //api.cybersource.com
        endif;

        //OAuth related config
        $this->enableClientCert = false;
        $this->clientCertDirectory = "Resources/";
        $this->clientCertFile = "";
        $this->clientCertPassword = "";
        $this->clientId = "";
        $this->clientSecret = "";


    }
    
    //creating merchant config object
    function merchantConfigObject()
    {
        if (!isset($this->merchantConfig)) {
            $config = $this->_cyberSourceMerchantConfiguration;
            $config->setauthenticationType(strtoupper(trim($this->authType)));
            $config->setMerchantID(trim($this->merchantID));
            $config->setApiKeyID($this->apiKeyID);
            $config->setSecretKey($this->secretKey);
            $config->setKeyFileName(trim($this->keyFilename));
            $config->setKeyAlias($this->keyAlias);
            $config->setKeyPassword($this->keyPass);
            $config->setUseMetaKey($this->useMetaKey);
            $config->setPortfolioID($this->portfolioID);
            $config->setRunEnvironment($this->runEnv);

            $config->validateMerchantData();
            $this->merchantConfig = $config;
        } else {
            return $this->merchantConfig;
        }
    }

    function ConnectionHost()
    {
        $merchantConf = $this->merchantConfigObject();
        $config = $this->_cyberSourceConfiguration;
        $config->setHost($merchantConf->getHost());
        return $config;
    }

    public function getConfig($config_path) 
    {
        return $this->_scopeConfig->getValue($config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }
    
}
