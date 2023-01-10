<?php

namespace Int\Configurator\Model;

use Int\Configurator\Api\Data\StoreInterface;
use Int\Configurator\Model\ResourceModel\Configurator as StoreResourceModel;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * OpeningTypes Model
 *
 * @method \Int\Configurator\Model\Resource\Page _getResource()
 * @method \Int\Configurator\Model\Resource\Page getResource()
 */
class OpeningTypes extends \Magento\Framework\Model\AbstractModel
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
        $this->_init('Int\Configurator\Model\ResourceModel\OpeningTypes');
    }
    

}
