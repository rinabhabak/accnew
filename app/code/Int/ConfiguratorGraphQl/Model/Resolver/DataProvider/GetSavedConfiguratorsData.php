<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ConfiguratorGraphQl\Model\Resolver\DataProvider;

use Int\Configurator\Model\ResourceModel\Configurator\CollectionFactory as ConfiguratorCollection;

class GetSavedConfiguratorsData
{
    /**
     * @var ConfiguratorCollection
     */
    protected $_configuratorCollection;

    /**
     * @param ConfiguratorCollection $configuratorCollection
     */
    public function __construct(
        ConfiguratorCollection $configuratorCollection
    ){
        $this->_configuratorCollection = $configuratorCollection;
    }

    public function getGetSavedConfiguratorsData($customerId,$no_of_configurator)
    {
        $configuration = [];

        if(empty($customerId)){
            throw new \Exception('The current customer isn\'t authorized.');
        }

        $_configuratorCollection = $this->_configuratorCollection->create()->addFieldToFilter('customer_id',$customerId);
		$_configuratorCollection->addFieldToFilter('status', ['in' => array(1,2)]);
        $_configuratorCollection->setPageSize($no_of_configurator);
        $_configuratorCollection->setOrder('updated_at','DESC')->load();

        if($_configuratorCollection->count() < 1){
            throw new \Exception('No configuration found. Create new configuration.');
        }

        foreach ($_configuratorCollection as $key => $value)
        {
            if(!$value->getId()){
                continue;
            }
            
            $configuration[$key]['project_id'] = $value->getProjectId();
            $configuration[$key]['configurator_id'] = (int) $value->getId();
            $configuration[$key]['project_name'] = $value->getProjectName();
            $configuration[$key]['last_save_at'] = (string) $value->getUpdatedAt();
        }
        
        return (array) $configuration;
    }
}