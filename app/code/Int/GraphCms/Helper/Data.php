<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Int\GraphCms\Helper;

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

    public function getBaseLinkUrl(){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
    }

    public function getEndPoint()
    {
        return $this->getStoreConfigValue('graphcms/general/endpoint');
    }

    public function getAuthKey()
    {
        return $this->getStoreConfigValue('graphcms/general/authkey');
    }
}
