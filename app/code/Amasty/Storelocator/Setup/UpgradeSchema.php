<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Media;
use Magento\Framework\DB\Adapter\AdapterInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\CreateAttributeTables
     */
    private $createAttributeTables;

    public function __construct(\Amasty\Storelocator\Setup\Operation\CreateAttributeTables $createAttributeTables)
    {
        $this->createAttributeTables = $createAttributeTables;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion()) {
            if (version_compare($context->getVersion(), '1.1.0', '<')) {
                $this->addStoreIds($setup);
            }
            if (version_compare($context->getVersion(), '1.2.0', '<')) {
                $this->addTimeSchedule($setup);
                $this->createAttributeTables->execute($setup);
            }
            if (version_compare($context->getVersion(), '1.3.0', '<')) {
                $this->addMarkerImg($setup);
            }
            if (version_compare($context->getVersion(), '1.5.2', '<')) {
                $this->changeLocationColumns($setup);
            }
            if (version_compare($context->getVersion(), '1.8.0', '<')) {
                $this->addShowSchedule($setup);
            }
        }

        $setup->endSetup();
    }

    private function addStoreIds(SchemaSetupInterface $setup)
    {
        $locationTable = $setup->getTable('amasty_amlocator_location');
        $setup->getConnection()->addColumn(
            $locationTable,
            'stores',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'Stores Ids'
            ]
        );

        if ($setup->getConnection()->tableColumnExists($locationTable, 'actions_serialize')) {
            $setup->getConnection()->changeColumn(
                $locationTable,
                'actions_serialize',
                'actions_serialized',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment'  => 'Actions Serialized'
                ]
            );
        }

        $setup->getConnection()->dropTable(
            $setup->getTable('amasty_amlocator_location_category')
        );
        $setup->getConnection()->dropTable(
            $setup->getTable('amasty_amlocator_location_product')
        );
        $setup->getConnection()->dropTable(
            $setup->getTable('amasty_amlocator_location_store')
        );
    }

    private function addTimeSchedule(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amlocator_location'),
            'schedule',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'Stores Schedule'
            ]
        );
    }

    private function addMarkerImg(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amlocator_location'),
            'marker_img',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'Marker Image'
            ]
        );
    }

    private function changeLocationColumns(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('amasty_amlocator_location'),
            'lat',
            'lat',
            [
                'type'   => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '11,8'
            ]
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable('amasty_amlocator_location'),
            'lng',
            'lng',
            [
                'type'   => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '11,8'
            ]
        );
    }

    private function addShowSchedule(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amlocator_location'),
            'show_schedule',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'unsigned' => true,
                'nullable' => false,
                'default'  => '1',
                'comment'  => 'Show schedule'
            ]
        );
    }

}
