<?php

namespace Int\Configurator\Model\ResourceModel;

/**
 * OpeningTypes Resource Model
 */
class OpeningTypes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('configurator_fixtures_opening_types', 'opening_type_id');
    }
}
