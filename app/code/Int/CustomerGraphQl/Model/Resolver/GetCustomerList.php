<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\CustomerGraphQl\Model\Resolver;

//resolver section
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Exception;

use Magento\Customer\Model\CustomerFactory as CustomerModel;

class GetCustomerList implements ResolverInterface
{
    protected $_customer;
    protected $_customerFactory;
    protected $groupRepository;
    /**
     * @var BdmManagerCollection
     */
    protected $_bdmManagerCollection;

    /**
     * @var ConfiguratorStatus
     */
    protected $_configuratorStatus;

    /**
     * @var ConfiguratorModel
     */
    protected $_configurator;
    
    /**
     * BdmManagement Helper
     *
     * @var \Int\BdmManagement\Helper\Data
     */
    protected $_bdmHelper;
    
    /**
     * Store Repository Interface
     *
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $_storeRepository;

    public function __construct(       
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers,
        \Int\BdmManagement\Helper\Data $bdmHelper,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->_customer = $customers;
        $this->groupRepository = $groupRepository;
        $this->_bdmHelper = $bdmHelper;
        $this->_storeRepository= $storeRepository;
    }
    
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $custDatas = array();
        $headers = getallheaders();
        //$storeCode = $headers['store'];
        $store = $this->_storeRepository->get("senseon_configurator_store_view");
        $websiteId = $store->getWebsiteId();
       
       
        $bdsGroups = $this->_bdmHelper->getAllowedGroups();
        $customerCollection =  $this->_customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter("website_id", array("eq" => $websiteId))           
                ->addAttributeToFilter("group_id", array('nin' => $bdsGroups))->setOrder('entity_id','DESC');
		
        foreach ($customerCollection as $customer) {
            $custDatas[$customer->getId()] = $customer->getData();
            $custDatas[$customer->getId()]['user_id'] = $customer->getId();
            $custDatas[$customer->getId()]['customerGroup'] = $this->getGroupName($customer->getGroupId());;
            $custDatas[$customer->getId()]['firstname'] = $customer->getFirstname();
            $custDatas[$customer->getId()]['lastname'] = $customer->getLastname();
			$custDatas[$customer->getId()]['email'] = $customer->getEmail();

        }
        
        return $custDatas;
    }

  
	public function getGroupName($groupId){
        $group = $this->groupRepository->getById($groupId);
        return $group->getCode();
    }


}