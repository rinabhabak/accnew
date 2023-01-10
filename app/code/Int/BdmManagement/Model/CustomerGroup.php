<?php

namespace Int\BdmManagement\Model;

/**
 * Status Model
 */
class CustomerGroup extends \Magento\Framework\Model\AbstractModel
{
    
    /**
     * Resource model instance
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $_resource;

    /**
     * Resource collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_resourceCollection;

    /**
     * Group Collection Factory
     *
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $groupCollectionFactory;
    
    /**
     * BdmManagement Helper
     *
     * @var \Int\BdmManagement\Helper\Data
     */
    protected $_bdmHelper;
    
    
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,
        \Int\BdmManagement\Helper\Data $bdmHelper,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_resource = $resource;
        $this->_resourceCollection = $resourceCollection;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->_bdmHelper = $bdmHelper;
        
        parent::__construct($context,$registry,$resource,$resourceCollection);
    }
    
    
    public function getCustomerGroups(){
        return $this->groupCollectionFactory->create();
    }
    
    
    public function toOptionArray(){
        $options = array();
        $options[] = ['value' => '', 'label' => 'Select an option'];
        $customerGroups = $this->getCustomerGroups();
        $bdsGroup = $this->_bdmHelper->getAllowedGroups();
        
        foreach($customerGroups as $customerGroup) {
            if(in_array($customerGroup->getId(),$bdsGroup)){
                $options[] = ['value' => $customerGroup->getId(), 'label' => $customerGroup->getCode()];
            }
        }
       
       return $options;
    }
    
    
    public function getOptionArray(){
        
        $options = array();
        $customerGroups = $this->getCustomerGroups();
        $bdsGroup = $this->_bdmHelper->getAllowedGroups();
        
        foreach($customerGroups as $customerGroup) {
            if(in_array($customerGroup->getId(),$bdsGroup)){
                $options[$customerGroup->getId()] = $customerGroup->getCode();
            }
        }
       
       return $options;
        
    }
    
    
    
    public function getCustomerGroupIds(){
        
        $options = array();
        $customerGroups = $this->getCustomerGroups();
        $bdsGroup = $this->_bdmHelper->getAllowedGroups();
        
        foreach($customerGroups as $customerGroup) {
            if(in_array($customerGroup->getId(),$bdsGroup)){
                $options[] = $customerGroup->getId();
            }
        }
       
        return $options;
    }
    
    
}