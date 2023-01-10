<?php
namespace Int\Configurator\Block\Adminhtml\Items;

class Index extends \Magento\Backend\Block\Widget\Container
{
    /*
     * \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory 
     **/
    
	public $customerFactory;
    
    /*
     * \Int\BdmManagement\Helper\Data
     **/
    public $_bdmHelper;
    
    /*
     * \Magento\Customer\Model\ResourceModel\GroupRepository
     **/
    protected $groupRepository;
    
    /*
     * \Int\CustomerGraphQl\Model\Resolver\GetBdmList
     **/
    protected $bdmList;
    
    /*
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Int\BdmManagement\Helper\Data $bdmHelper
     * @param \Int\CustomerGraphQl\Model\Resolver\GetBdmList $bdmList
    **/	
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Int\BdmManagement\Helper\Data $bdmHelper,
        \Int\CustomerGraphQl\Model\Resolver\GetBdmList $bdmList,
        array $data = []
    )
    {
		$this->customerFactory = $customerFactory;
        $this->_bdmHelper = $bdmHelper;
        $this->groupRepository = $groupRepository;
        $this->bdmList = $bdmList;
        parent::__construct($context, $data);
    }
	
	/**
	 * Get customer groups
	 * 
	 * @return array
	 */ 
	public function getCustomers() {
        $bdsGroup = $this->_bdmHelper->getBdsCustomerGroup();
		$customerCollection = $this->customerFactory->create()
                                ->addFieldToFilter('group_id', $bdsGroup);
		return $customerCollection;
	}
    
    
    
    /**
     * Retrieves customer group name
     * @param integer $groupId
     * @return string 
     */

    public function getGroupName($groupId){
        $group = $this->groupRepository->getById($groupId);
        return $group->getCode();
    }
    
    
    /*
     * Get Active Tasks of a BDM
     * @param integer $bdmId
     * @return integer
    **/
    public function getActiveTasks($bdmId){        
        return $this->bdmList->getActiveTasks($bdmId);        
    }
    
}