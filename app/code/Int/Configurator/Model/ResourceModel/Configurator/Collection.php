<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\Configurator\Model\ResourceModel\Configurator;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'configurator_id';
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Int\Configurator\Model\Configurator',
            'Int\Configurator\Model\ResourceModel\Configurator'
        );
    }
}