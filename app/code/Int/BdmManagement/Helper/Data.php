<?php

/**
 * BdmManagement data helper
 */
namespace Int\BdmManagement\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }
    
    
    /**
     * Return config path value
     * @return miscellaneous
     */
    public function getConfigValue($path){
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    
    /**
     * Get Allowed Groups
     * @return integer
     */
    public function getAllowedGroups(){
        $allowed_groups = self::getConfigValue('bdm/general/allowed_groups');
        $allowed_groups = explode(',',$allowed_groups);
        return $allowed_groups;
    }
    
    
    /**
     * Get BDM customer group
     * @return integer
     */
    public function getBdsCustomerGroup(){
        return self::getConfigValue('bdm/general/bds_customer_group');
    }
    
    
    /**
     * Get BDM Manager customer group
     * @return integer
     */
    public function getBdmCustomerGroup(){
        return self::getConfigValue('bdm/general/bdm_manager_customer_group');
    }
    
    
}
