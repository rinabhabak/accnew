<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


declare(strict_types=1);

namespace Amasty\Acart\Model\Rule\Relation;

use Magento\Framework\App\ResourceConnection;
use Amasty\Acart\Setup\Operation\CreateRuleStoreTable;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

class StoreProcessor implements RelationInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function processRelation(\Magento\Framework\Model\AbstractModel $rule)
    {
        $this->resourceConnection->getConnection()->delete(
            $this->resourceConnection->getTableName(CreateRuleStoreTable::TABLE_NAME),
            $this->resourceConnection->getConnection()->quoteInto('rule_id = ?', (int)$rule->getRuleId())
        );
        $storeAssociationData = array_map(function ($storeId) use ($rule) {
            return [
                'rule_id' => (int)$rule->getRuleId(),
                'store_id' => (int)$storeId
            ];
        }, $rule->getData(\Amasty\Acart\Model\Rule::STORE_IDS) ?: []);

        if ($storeAssociationData) {
            $this->resourceConnection->getConnection()->insertOnDuplicate(
                $this->resourceConnection->getTableName(CreateRuleStoreTable::TABLE_NAME),
                $storeAssociationData
            );
        }
    }
}
