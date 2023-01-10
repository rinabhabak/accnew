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
use Int\Configurator\Model\BdmManagersFactory as BdmManagersModel;

class BdmPastConfiguratorsList
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
     * @var CustomerModel
     */
    protected $_customer;

    /**
     * @var BdmManagersModel
     */
    protected $_bdmManagers;


    /**
     * @param BdmManagerCollection $bdmManagerCollection
     * @param ConfiguratorModel $configuratorFactory
     * @param ConfiguratorStatus $configuratorStatus
     * @param CustomerModel $customerModel
     */
    public function __construct(
        BdmManagerCollection $bdmManagerCollection,
        ConfiguratorModel $configuratorFactory,
        ConfiguratorStatus $configuratorStatus,
        CustomerModel $customerModel,
        BdmManagersModel $bdmManagersModel
    ){
        $this->_bdmManagerCollection = $bdmManagerCollection;
        $this->_configurator = $configuratorFactory;
        $this->_configuratorStatus = $configuratorStatus;
        $this->_customer = $customerModel;
        $this->_bdmManagers = $bdmManagersModel;
    }

    public function getGetConfiguratorList($bdmId,$status)
    {
        $configuration = [];

        if(empty($bdmId)){
            throw new \Exception('BDM Id is required.');
        }


        $_configurators = $this->_configurator->create()->getCollection();

        $_configurators->getSelect()->joinLeft(
               ['bdm_managers_assigned'=>$_configurators->getTable('configurator_assigned_bdm_managers')],
               'main_table.configurator_id = bdm_managers_assigned.parent_id',
               ['assigned_bdm_managers'=>'bdm_managers_assigned.assigned_to']);

        $past_date = date('Y-m-d', strtotime('-15 days'));
        $past_date = $past_date.' 23:59:59';

        if($status!=='' && $status!==NULL){
            $_configurators->getSelect()->where("bdm_managers_assigned.assigned_to=".$bdmId." AND main_table.created_at<='".$past_date."' AND main_table.status=".$status);
        }else{
            $_configurators->getSelect()->where("bdm_managers_assigned.assigned_to=".$bdmId." AND main_table.created_at<='".$past_date."'");
        }

        $_configurators->getSelect()->order('main_table.created_at','DESC');

        if($_configurators->count() < 1){
            throw new \Exception('No configuration found. Please Create new configuration.');
        }

        foreach ($_configurators as $key => $_configurator)
        {
            if(!$_configurator->getId()){
                continue;
            }

            $configuration[$key]['configurator_id'] = $_configurator->getId();
			$configuration[$key]['project_id'] = $_configurator->getProjectId();
			$configuration[$key]['project_name'] = $_configurator->getProjectName();
            $configuration[$key]['customer_id'] = $_configurator->getCustomerId();
            $configuration[$key]['customer_name'] = $this->getCustomerName($_configurator->getCustomerId());
            $configuration[$key]['type_of_build'] = $_configurator->getTypeOfBuild();
            $configuration[$key]['status'] = $this->_configuratorStatus->getOptionText($_configurator->getStatus());
            $configuration[$key]['numbers_of_fixture'] = $_configurator->getNumbersOfFixture();
            $configuration[$key]['same_fixture_dimensions'] = ($_configurator->getSameFixtureDimensions() == 1) ? true : false;
            $configuration[$key]['creation_date'] = $_configurator->getCreatedAt();
        }

        return (array) $configuration;
    }

    protected function getCustomerName($customerId)
    {
        $customer = $this->_customer->create()->load($customerId);

        if(!$customer->getId()){
            return 'NA';
        }

        return $customer->getName();
    }

    
}

