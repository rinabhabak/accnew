<?php

namespace Int\Configurator\Model\ResourceModel;

/**
 * Fixture Resource Model
 */
class Fixture extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('configurator_fixtures', 'fixture_id');
    }
}
