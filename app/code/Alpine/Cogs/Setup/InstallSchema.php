<?php
/**
 * Alpine_Cogs
 *
 * @category    Alpine
 * @package     Alpine_Cogs
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Evgeniy Derevyanko <evgeniy.derevyanko@alpineinc.com>
 */
namespace Alpine\Cogs\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
/**
 * Initial install schema class
 * 
 * @category    Alpine
 * @package     Alpine_Cogs
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installation method
     * 
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        SchemaSetupInterface $setup, 
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
 
        $tableName = $setup->getTable('sales_order_grid');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $columns = [
                'is_exported' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'default' => 0,
                    'comment' => 'export flag',
                ],
            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($tableName, $name, $definition);
            }
        }

        $tableName = $setup->getTable('magento_rma_grid');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $columns = [
                'is_exported' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'default' => 0,
                    'comment' => 'export flag',
                ],
            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($tableName, $name, $definition);
            }
        }
 
        $setup->endSetup();
    }
}
