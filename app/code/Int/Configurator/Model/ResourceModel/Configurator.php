<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\Configurator\Model\ResourceModel;

class Configurator extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('configurator', 'configurator_id');   //here "int_configurator" is table name and "configurator_id" is the primary key of custom table
    }
}