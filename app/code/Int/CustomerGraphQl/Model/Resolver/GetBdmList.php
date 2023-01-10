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
use Int\Configurator\Model\ResourceModel\BdmManagers\CollectionFactory as BdmManagerCollection;
use Int\Configurator\Model\Status as ConfiguratorStatus;
use Int\Configurator\Model\ConfiguratorFactory as ConfiguratorModel;
use Magento\Customer\Model\CustomerFactory as CustomerModel;

class GetBdmList implements ResolverInterface
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

    public function __construct(
        BdmManagerCollection $bdmManagerCollection,
        ConfiguratorModel $configuratorFactory,
        ConfiguratorStatus $configuratorStatus,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers,
        \Int\BdmManagement\Helper\Data $bdmHelper
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->_customer = $customers;
        $this->groupRepository = $groupRepository;
        $this->_bdmManagerCollection = $bdmManagerCollection;
        $this->_configurator = $configuratorFactory;
        $this->_configuratorStatus = $configuratorStatus;
        $this->_bdmHelper = $bdmHelper;
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
        $bdmDatas = array();
        $bdsGroup = $this->_bdmHelper->getBdsCustomerGroup();
        $customerCollection =  $this->_customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter("group_id", array("eq" => $bdsGroup));

        foreach ($customerCollection as $customer) {
            $bdmDatas[$customer->getId()] = $customer->getData();
            $bdmDatas[$customer->getId()]['user_id'] = $customer->getId();
            $bdmDatas[$customer->getId()]['customerGroup'] = $this->getGroupName($customer->getGroupId());
            $bdmDatas[$customer->getId()]['active_task'] = $this->getActiveTasks($customer->getId());
            $bdmDatas[$customer->getId()]['completed_task'] = $this->getCompletedTasks($customer->getId());
            $bdmDatas[$customer->getId()]['title'] = __('Sales');

        }
        
        return $bdmDatas;
    }

    public function getCustomerCollection() {
        return $this->_customer->getCollection()
               ->addAttributeToSelect("*")
               ->load();
    }

    public function getFilteredCustomerCollection() {
        $bdsGroup = $this->_bdmHelper->getBdsCustomerGroup();
        return $this->_customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter("group_id", array("eq" => $bdsGroup));
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

        if(empty($bdmId)){
            throw new \Exception('BDM Id is required.');
        }

        $collection = $this->_bdmManagerCollection->create();

        $collection->getSelect()->join(
            ['config' => $collection->getTable('configurator')],
            'config.configurator_id = main_table.parent_id',
            []
        )->where(
            "main_table.assigned_to=".$bdmId
        );
        
        $data = $collection->getAllIds();

        return count($data);

    }


    /*
     * Get Completed Tasks of a BDM
     * @param integer $bdmId
     * @return integer
    **/


    protected function getCompletedTasks($bdmId){

        if(empty($bdmId)){
            throw new \Exception('BDM Id is required.');
        }

        $collection = $this->_bdmManagerCollection->create();

        $collection->getSelect()->join(
            ['config' => $collection->getTable('configurator')],
            'config.configurator_id = main_table.parent_id',
            []
        )->where(
            "config.status=".\Int\Configurator\Model\Status::STATUS_COMPLETE." AND main_table.assigned_to=".$bdmId
        );

         $data = $collection->getAllIds();

         return count($data);

    }



}