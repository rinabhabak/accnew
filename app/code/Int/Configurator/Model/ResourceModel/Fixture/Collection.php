<?php

/**
 * Configurator Resource Collection
 */
namespace Int\Configurator\Model\ResourceModel\Fixture;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Int\Configurator\Model\Fixture', 'Int\Configurator\Model\ResourceModel\Fixture');
    }
}
