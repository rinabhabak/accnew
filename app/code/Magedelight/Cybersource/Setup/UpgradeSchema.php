<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Cybersource
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Cybersource\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('magedelight_cybersource');
        
        if (version_compare($context->getVersion(), '1.0.3', '<' )) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $installer->getConnection()->addColumn(
                    $tableName,
                    'website_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 10,
                        'nullable' => true,
                        'afters' => 'card_id',
                        'comment' => 'Customer Website Id'
                    ]
                );
            }
        }
        /* upgrade primary key range */
        if (version_compare($context->getVersion(), '1.0.9', '>' )) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                
                $installer->getConnection()->changeColumn(
                    $setup->getTable($tableName),
                            'card_id',
                            'card_id',
                            [
                                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                'length' => 10,
                                'nullable' => false,
                                'primary' => true,
                                'identity' => true
                            ]
                );
            }
        }
        $setup->endSetup();

    }
}