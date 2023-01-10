<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


declare(strict_types=1);

namespace Amasty\Acart\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateRuleCustomerGroupTable
{
    const TABLE_NAME = 'amasty_acart_rule_customer_group';
    const RELATION_ID_FIELD = 'rule_customer_group_id';

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
        $customerGroupTable = $setup->getTable('customer_group');

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Acart Rule-Customer Relation Table'
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
                'Rule-Customer Relation Id'
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
                'customer_group_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Customer Group Id'
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
                    'customer_group_id',
                    $customerGroupTable,
                    'customer_group_id'
                ),
                'customer_group_id',
                $customerGroupTable,
                'customer_group_id',
                Table::ACTION_CASCADE
            );
    }
}
