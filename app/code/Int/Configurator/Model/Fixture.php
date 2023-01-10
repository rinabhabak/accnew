<?php

namespace Int\Configurator\Model;

/**
 * Fixture Model
 *
 * @method \Int\Configurator\Model\Resource\Page _getResource()
 * @method \Int\Configurator\Model\Resource\Page getResource()
 */
class Fixture extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_PENDING = 0;
    const STATUS_COMPLETE = 1;
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Int\Configurator\Model\ResourceModel\Fixture');
    }

}
