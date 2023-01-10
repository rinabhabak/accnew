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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
 
/**
 * Initial install schema class
 *
 * @category    Alpine
 * @package     Alpine_Cogs 
 */
class UpgradeSchema implements  UpgradeSchemaInterface
{
    /**
     * Installation method
     * 
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function Upgrade(
        SchemaSetupInterface $setup, 
        ModuleContextInterface $context
    ) {        
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.1.1') < 0) {
            $tableName = $setup->getTable('sales_order');
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

            $tableName = $setup->getTable('magento_rma');
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
        }

        $setup->endSetup();
    }
}
