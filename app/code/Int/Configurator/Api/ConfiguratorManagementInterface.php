<?php

namespace Int\Configurator\Api;

interface ConfiguratorManagementInterface
{
    /**
     * get Configurator Api data.
     *
     * @api
     *
     * @param int $configurator_id
     * @param int $fixture_id
     *
     * @return string
     */
    public function getPreview($configurator_id,$fixture_id);
	
	/**
     * set 3dConfigurator Api data.
     *
     * @api
     *
     * @param string 
     *
     * @return mixed
     */
	
	public function set3dConfigurators();
	
	 /**
     * get Configurator Api data.
     *
     * @api
     *
     * @param int $configurator_id
	 *
     * @param int $fixture_id
     *
     * @return string
     */
    public function getConfigurators3dPreview($configurator_id,  $fixture_id);
}