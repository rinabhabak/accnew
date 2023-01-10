<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ConfiguratorGraphQl\Model\Resolver\DataProvider;

use Exception;
use Int\Configurator\Model\Status as ConfiguratorStatus;
use Int\Configurator\Model\ConfiguratorFactory as ConfiguratorModel;
use Int\Configurator\Model\FixtureFactory as FixtureModel;

class GetBdmManagerConfiguratorsDetails
{
    /**
     * @var ConfiguratorModel
     */
    protected $_configurator;

    /**
     * @var ConfiguratorStatus
     */
    protected $_configuratorStatus;

    /**
     * @var FixtureModel
     */
    protected $_fixture;

    /**
     * @param ConfiguratorCollection $configuratorCollection
     * @param ConfiguratorStatus $configuratorStatus
     * @param FixtureModel $fixtureModel
     */
    public function __construct(
        ConfiguratorModel $configuratorFactory,
        ConfiguratorStatus $configuratorStatus,
        FixtureModel $fixtureModel
    ){
        $this->_configurator = $configuratorFactory;
        $this->_configuratorStatus = $configuratorStatus;
        $this->_fixture = $fixtureModel;
    }

    public function getGetConfiguratorData($configuratorId)
    {
        $configuration = [];

        $configuratorData = $this->_configurator->create()->load($configuratorId);

        if(!$configuratorData->getId()){
            throw new \Exception('No configuration found. Please Create new configuration.');
        }

        $configuration['configurator_id'] = $configuratorData->getId();
		$configuration['project_id'] = $configuratorData->getProjectId();
        $configuration['customer_id'] = $configuratorData->getCustomerId();
        $configuration['type_of_build'] = $configuratorData->getTypeOfBuild();
        $configuration['status'] = $this->_configuratorStatus->getOptionText($configuratorData->getStatus());
        $configuration['numbers_of_fixture'] = $configuratorData->getNumbersOfFixture();
        $configuration['fixtures'] = $this->getFixtureData($configuratorId);
        $configuration['same_fixture_dimensions'] = ($configuratorData->getSameFixtureDimensions() == 1) ? true : false;
        $configuration['created_at'] = $configuratorData->getCreatedAt();
        $configuration['updated_at'] = $configuratorData->getUpdatedAt();

        return (array) $configuration;
    }

    protected function getFixtureData($configuratorId)
    {
        $fixtures = $this->_fixture->create()->getCollection()->addFieldToFilter('configurator_id',$configuratorId);
        
        if($fixtures->count() > 0){
            return $fixtures->getData();
        }
        
        return [];
    }
}

