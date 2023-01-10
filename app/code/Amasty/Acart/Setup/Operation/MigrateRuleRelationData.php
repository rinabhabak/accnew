<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


declare(strict_types=1);

namespace Amasty\Acart\Setup\Operation;

use Amasty\Acart\Model\Rule as RuleModel;
use Amasty\Acart\Model\ResourceModel\Rule;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\SchemaSetupInterface;

class MigrateRuleRelationData
{
    const OLD_STORE_IDS_ALIAS = 'old_store_ids';
    const OLD_CUSTOMER_GROUP_IDS_ALIAS = 'old_customer_ids';

    /**
     * @var Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    public function __construct(
        Rule\CollectionFactory $ruleCollectionFactory
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        try {
            $ruleCollection = $this->ruleCollectionFactory->create();
            $ruleCollection->getSelect()
                ->reset(Select::COLUMNS)
                ->columns([
                    RuleModel::RULE_ID => RuleModel::RULE_ID,
                    self::OLD_STORE_IDS_ALIAS => RuleModel::STORE_IDS,
                    self::OLD_CUSTOMER_GROUP_IDS_ALIAS => RuleModel::CUSTOMER_GROUP_IDS,
                ]);
            $this->migrateStoreIds($setup, $ruleCollection);
            $this->migrateCustomerGroupIds($setup, $ruleCollection);
        } catch (\Exception $e) {
            null;
        }
    }

    private function migrateStoreIds(SchemaSetupInterface $setup, Rule\Collection $ruleCollection)
    {
        $relationData = [];

        /** @var \Amasty\Acart\Model\Rule $rule */
        foreach ($ruleCollection as $rule) {
            $storeIds = explode(',', $rule->getData(self::OLD_STORE_IDS_ALIAS));

            foreach ($storeIds as $storeId) {
                $relationData[] = [
                    'rule_id' => (int)$rule->getRuleId(),
                    'store_id'=> (int)$storeId,
                ];
            }
        }

        if ($relationData) {
            $ruleStoreRelationTable = $setup->getTable(CreateRuleStoreTable::TABLE_NAME);
            $setup->getConnection()->insertMultiple($ruleStoreRelationTable, $relationData);
        }
    }

    private function migrateCustomerGroupIds(SchemaSetupInterface $setup, Rule\Collection $ruleCollection)
    {
        $relationData = [];

        /** @var \Amasty\Acart\Model\Rule $rule */
        foreach ($ruleCollection as $rule) {
            $customerGroupIds = explode(',', $rule->getData(self::OLD_CUSTOMER_GROUP_IDS_ALIAS));

            foreach ($customerGroupIds as $customerGroupId) {
                $relationData[] = [
                    'rule_id' => (int)$rule->getRuleId(),
                    'customer_group_id' => (int)$customerGroupId,
                ];
            }
        }

        if ($relationData) {
            $ruleCustomerGroupRelationTable = $setup->getTable(CreateRuleCustomerGroupTable::TABLE_NAME);
            $setup->getConnection()->insertMultiple($ruleCustomerGroupRelationTable, $relationData);
        }
    }
}
