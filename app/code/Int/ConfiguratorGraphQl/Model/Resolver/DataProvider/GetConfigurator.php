<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ConfiguratorGraphQl\Model\Resolver\DataProvider;

use Exception;
use Int\Configurator\Model\ResourceModel\Configurator\Collection as ConfiguratorCollection;
use Int\Configurator\Model\Status as ConfiguratorStatus;

class GetConfigurator
{
    /**
     * @var ConfiguratorCollection
     */
    protected $_configuratorCollection;

    /**
     * @var ConfiguratorStatus
     */
    protected $_configuratorStatus;

    /**
     * @var ConfiguratorProductHelper;
     */
    protected $_configuratorProductHelper;
    
    /**
     * @var \Int\Configurator\Helper\Data;
     */
    protected $_configuratorHelper;

    /**
     * @param ConfiguratorCollection $configuratorCollection
     * @param ConfiguratorStatus $configuratorStatus
     */
    public function __construct(
        ConfiguratorCollection $configuratorCollection,
        ConfiguratorStatus $configuratorStatus,
        \Int\Configurator\Helper\Data $configuratorHelper,
        \Int\Configurator\Helper\Products $configuratorProductHelper
    ){
        $this->_configuratorCollection = $configuratorCollection;
        $this->_configuratorStatus = $configuratorStatus;
        $this->_configuratorProductHelper = $configuratorProductHelper;
        $this->_configuratorHelper = $configuratorHelper;
    }

    public function getGetConfigurator($customer_id)
    {
        $configuration = [];

        if(empty($customer_id)){
            throw new \Exception('The current customer isn\'t authorized.');
        }

        $collection = $this->_configuratorCollection->addFieldToFilter('customer_id', $customer_id)->setOrder('configurator_id','DESC');

        if($collection->count() < 1){
            throw new \Exception('No configuration found. Please Create new configuration.');
        }

        foreach ($collection as $key => $value)
        {
            if(!$value->getId()){
                continue;
            }
            $configuration[$key] = $value->getData();
            $configuration[$key]['project_id'] = $value->getId();
			$configuration[$key]['project_no'] = $value->getProjectId();
            $configuration[$key]['project_name'] = $value->getProjectName();
            $configuration[$key]['status'] = $this->_configuratorStatus->getOptionText($value->getStatus());
            //$configuration[$key]['numbers_of_fixture'] = $value->getNumbersOfFixture();
            $configuration[$key]['same_fixture_dimensions'] = ($value->getSameFixtureDimensions() == 1) ? true : false;
            $configuration[$key]['is_salable'] = $this->getIsSalable($value->getId(), $value->getStatus());
            $configuration[$key]['order_items'] = $this->getItems($value->getId(), $value->getStatus());
        }

        return (array) $configuration;
    }

    public function getFixturesData($configuratorId)
    {
        $products = $this->_configuratorHelper->getProductList($configuratorId);
        return $products;
    }
    
    public function getProductList($configuratorId)
    {
        $products = $this->_configuratorProductHelper->getProductLists($configuratorId);

        return $products;
    }
    

    public function getIsSalable($configuratorId, $status)
    {
        $complete = ConfiguratorStatus::STATUS_COMPLETE;
        $purchased = ConfiguratorStatus::STATUS_PURCHASED;
        $isSalable = 1;
        if($status == $complete || $status == $purchased) {
            $configuratorProducts = $this->getFixturesData($configuratorId);
            
            if(isset($configuratorProducts['fixtures'])){
                $fixtures = $configuratorProducts['fixtures'];
                foreach($fixtures as $fixture) {
                    
                    if($fixture['is_senseon_plus']==1){
                        $isSalable = 0;
                        break;
                    }
                    
                }
            }else{
                $isSalable = 0;
            }
            
        } else {
            $isSalable = 0;
        }
        
        return $isSalable;
    }

    public function getItems($configuratorId, $status)
    {
        $complete = $this->_configuratorStatus::STATUS_COMPLETE;
        $purchased = $this->_configuratorStatus::STATUS_PURCHASED;

        $items = array();

        if($status == $complete || $status == $purchased) {
            $products = $this->getProductList($configuratorId);

            foreach($products as $product) {
                $productKey = array_keys($product);
                foreach($productKey as $value) {
                    $items[] = array(
                        'id' => $product[$value]['id'],
                        'name' => $product[$value]['name'],
                        'sku' => $product[$value]['sku'],
                        'qty' => $product[$value]['qty'],
                        'uom' => $product[$value]['uom'],
                        'price' => $product[$value]['price'],
                        'row_total' => $product[$value]['row_total'],
                        'row_total_formatted' => $product[$value]['row_total_formatted'],
                        'is_salable' => $product[$value]['is_salable']
                    );
                }
            }
        }

        return $items;
    }
}