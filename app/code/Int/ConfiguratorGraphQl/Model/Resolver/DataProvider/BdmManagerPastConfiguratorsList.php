<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ConfiguratorGraphQl\Model\Resolver\DataProvider;

use Exception;
use Int\Configurator\Model\ResourceModel\BdmManagers\Collection as BdmManagerCollection;
use Int\Configurator\Model\Status as ConfiguratorStatus;
use Int\Configurator\Model\ConfiguratorFactory as ConfiguratorModel;
use Magento\Customer\Model\CustomerFactory as CustomerModel;
use Int\Configurator\Model\BdmManagersFactory as BdmManagersFactory;

class BdmManagerPastConfiguratorsList
{
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
     * @var BdmManagersFactory
     */
    protected $_bdmManagersFactory;

    /**
     * @var CustomerModel
     */
    protected $_customer;

    /**
     * @param BdmManagerCollection $bdmManagerCollection
     * @param ConfiguratorModel $configuratorFactory
     * @param ConfiguratorStatus $configuratorStatus
     * @param BdmManagersFactory $bdmManagersFactory
     * @param CustomerModel $customerModel
     */
    public function __construct(
        BdmManagerCollection $bdmManagerCollection,
        ConfiguratorModel $configuratorFactory,
        ConfiguratorStatus $configuratorStatus,
        BdmManagersFactory $bdmManagersFactory,
        CustomerModel $customerModel
    ){
        $this->_bdmManagerCollection = $bdmManagerCollection;
        $this->_configurator = $configuratorFactory;
        $this->_configuratorStatus = $configuratorStatus;
        $this->_customer = $customerModel;
        $this->_bdmManagersFactory = $bdmManagersFactory;
    }

    public function getGetConfiguratorList($status,$no_of_configurator)
    {
        $configuration = [];

        $collection = $this->_configurator->create()->getCollection();

        if($status!==NUll && $status!==''){
            $collection->addFieldToFilter('status',$status);
        }

       
        $past_date = date('Y-m-d', strtotime('-15 days'));
        $collection->addFieldToFilter('created_at',['lteq'=>$past_date.' 23:59:59']);
		$collection->setPageSize($no_of_configurator);
        $collection->setOrder('created_at','DESC');


        if($collection->count() < 1){
            throw new \Exception('No configuration found. Please Create new configuration.');
        }


        foreach ($collection as $key => $configurator)
        {
            if(!$configurator->getId()){
                continue;
            }

            $bdmManagerCollection = $this->_bdmManagersFactory->create()->getCollection();
            $bdmManagerCollection->addFieldToFilter('parent_id',$configurator->getId());
            $bdmManager = $bdmManagerCollection->getFirstItem();

            $configuration[$key]['configurator_id'] = $configurator->getId();
			$configuration[$key]['project_id'] = $configurator->getProjectId();
			$configuration[$key]['project_name'] = $configurator->getProjectName();
            $configuration[$key]['customer_id'] = $configurator->getCustomerId();
            $configuration[$key]['customer_name'] = ($configurator->getCustomerId())?$this->getCustomerName($configurator->getCustomerId()):'NA';
            $configuration[$key]['type_of_build'] = $configurator->getTypeOfBuild();
            $configuration[$key]['status'] = $this->_configuratorStatus->getOptionText($configurator->getStatus());
            $configuration[$key]['numbers_of_fixture'] = $configurator->getNumbersOfFixture();
            $configuration[$key]['same_fixture_dimensions'] = ($configurator->getSameFixtureDimensions() == 1) ? true : false;
            $configuration[$key]['creation_date'] = $configurator->getCreatedAt();
            $configuration[$key]['customer_name'] = ($configurator->getCustomerId())?$this->getCustomerName($configurator->getCustomerId()):'NA';

            if($bdmManager->getId()){
                $configuration[$key]['assigned_bdm_id'] = $bdmManager->getAssignedTo();
                $configuration[$key]['assigned_bdm_date'] = $bdmManager->getCreatedAt();
                $configuration[$key]['assigned_bdm_name'] = ($bdmManager->getAssignedTo())?$this->getCustomerName($bdmManager->getAssignedTo()):'NA';
            }else{
                $configuration[$key]['assigned_bdm_id'] = 0;
                $configuration[$key]['assigned_bdm_date'] = '';
                $configuration[$key]['assigned_bdm_name'] = '';
            }

        }

        return (array) $configuration;
    }

    protected function getCustomerName($customerId)
    {
        if(empty($customerId)){
            return new \Exception('Customer Id is required.');
        }

        $customer = $this->_customer->create()->load($customerId);

        if(!$customer->getId()){
            return 'NA';
        }

        return $customer->getName();
    }

    protected function getConfigurator($configuratorId)
    {
        if(empty($configuratorId)){
            return new \Exception('Configurator Id is required.');
        }

        return $this->_configurator->create()->load($configuratorId);
    }
}

