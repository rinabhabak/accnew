<?php
/**
 * Alpine_Storelocator
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Denis Furman <denis.furman@alpineinc.com>
 */


namespace Alpine\Storelocator\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amlocator_location'),
            'fax',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Fax']);

        $installer->endSetup();
    }


}