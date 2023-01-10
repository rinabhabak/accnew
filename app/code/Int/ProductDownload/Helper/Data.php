<?php
/**
 * @author Indusnet Team
 * @package Int_ProductDownload
 */

namespace Int\ProductDownload\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public $_storeManager;

    public function __construct(ScopeConfigInterface $scopeConfig, \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager=$storeManager;
    }

    public function getStoreConfigValue($configPath){
        return $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
    }

    public function getStoreUrl(){
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function getStoreLocatorUrl($configPath){
        return $this->getStoreUrl().$this->getStoreConfigValue($configPath);
    }
}
