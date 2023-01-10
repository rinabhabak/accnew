<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


declare(strict_types=1);

namespace Amasty\Acart\Setup\Operation;

use Amasty\Acart\Model\Rule;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateRuleStoreTable
{
    const TABLE_NAME = 'amasty_acart_rule_store';
    const RELATION_ID_FIELD = 'rule_store_id';

    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    private function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(self::TABLE_NAME);
        $ruleTable = $setup->getTable('amasty_acart_rule');
        $storeTable = $setup->getTable('store');

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Acart Rule-Store Relation Table'
            )->addColumn(
                self::RELATION_ID_FIELD,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Rule-Store Relation Id'
            )->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Rule Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Store Id'
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    'rule_id',
                    $ruleTable,
                    'rule_id'
                ),
                'rule_id',
                $ruleTable,
                'rule_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    'store_id',
                    $storeTable,
                    'store_id'
                ),
                'store_id',
                $storeTable,
                'store_id',
                Table::ACTION_CASCADE
            );
    }
}
