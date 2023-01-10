<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo190
{
    /**
     * @var CreateRuleStoreTable
     */
    private $createRuleStoreTable;

    /**
     * @var CreateRuleCustomerGroupTable
     */
    private $createRuleCustomerGroupTable;

    /**
     * @var MigrateRuleRelationData
     */
    private $migrateRuleRelationData;

    /**
     * @var DropRuleRelationColumns
     */
    private $dropRuleRelationColumns;

    public function __construct(
        CreateRuleStoreTable $createRuleStoreTable,
        CreateRuleCustomerGroupTable $createRuleCustomerGroupTable,
        MigrateRuleRelationData $migrateRuleRelationData,
        DropRuleRelationColumns $dropRuleRelationColumns
    ) {
        $this->createRuleStoreTable = $createRuleStoreTable;
        $this->createRuleCustomerGroupTable = $createRuleCustomerGroupTable;
        $this->migrateRuleRelationData = $migrateRuleRelationData;
        $this->dropRuleRelationColumns = $dropRuleRelationColumns;
    }

    public function execute(SchemaSetupInterface $setup)
    {
        $this->createRuleStoreTable->execute($setup);
        $this->createRuleCustomerGroupTable->execute($setup);
        $this->migrateRuleRelationData->execute($setup);
        $this->dropRuleRelationColumns->execute($setup);
    }
}
