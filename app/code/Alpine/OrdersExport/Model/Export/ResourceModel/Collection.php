<?php
/**
 * Resource model
 *
 * @category    Alpine
 * @package     Alpine_OrdersExport
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 * @author      Alex Didenko <alex.didenko@alpineinc.com>
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */

namespace Alpine\OrdersExport\Model\Export\ResourceModel;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Zend_Db;

/**
 * Alpine\OrdersExport\Model\Export\ResourceModel\Collection
 *
 * @category    Alpine
 * @package     Alpine_OrdersExport
 */
class Collection
{
    /**
     * Int entities
     *
     * @var array
     */
    protected $intEntities = [
        'slide_series',
        'account_number',
        'cost_center',
        'company_number',
        'subaccount',
        'project_number',
    ];

    /**
     * Varchar entities
     *
     * @var array
     */
    protected $varcharEntities = [
        'bbu',
    ];

    /**
     * Timezone
     *
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Constructor
     *
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

    /**
     * Get int entities
     *
     * @return array
     */
    protected function getIntEntities()
    {
        return $this->intEntities;
    }

    /**
     * Get varchar entities
     *
     * @return array
     */
    protected function getVarcharEntities()
    {
        return $this->varcharEntities;
    }

    /**
     * Apply filters
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $collection
     * @param array $filters
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function applyFilters($collection, array $filters = [])
    {
        if (!empty($filters)) {
            $tableKeyPrefix = 'main_table.';
            foreach ($filters as $key => $value) {
                if ($key === 'increment_id') {
                    $key = 'entity_id';
                    $collection->addFieldToFilter($tableKeyPrefix . $key, $value);
                }
                if ($key === 'store_id') {
                    $collection->addFieldToFilter($tableKeyPrefix . 'store_id', $value);
                }
                if ($key === 'created_at') {
                    $value['from'] = date('Y-m-d 00:00:00', strtotime($value['from']));
                    $value['from'] = $this->timezone->convertConfigTimeToUtc($value['from']);
                    if (isset($value['to'])) {
                        $value['to'] = date('Y-m-d 23:59:59', strtotime($value['to']));
                        $value['to'] = $this->timezone->convertConfigTimeToUtc($value['to']);
                    }
                    $collection->addFieldToFilter($tableKeyPrefix . $key, $value);
                }
                if ($key === 'completed_at') {
                    $collection->addFilterToMap('created_at', 'main_table.created_at');
                    $collection->addFilterToMap('completed_at', 'order_history.completed_at');

                    $select = $collection->getSelect();
                    $sales_order_status_history = $collection->getTable('sales_order_status_history');

                    $subquery = new \Zend_Db_Expr('(SELECT parent_id, MAX(created_at) AS completed_at FROM '. $sales_order_status_history .' GROUP BY parent_id)');

                    $select->joinLeft(
                        array( 'order_history' => $subquery ),
                        'main_table.entity_id=order_history.parent_id'
                    );

                    $value['from'] = date('Y-m-d 00:00:00', strtotime($value['from']));
                    $value['from'] = $this->timezone->convertConfigTimeToUtc($value['from']);
                    if (isset($value['to'])) {
                        $value['to'] = date('Y-m-d 23:59:59', strtotime($value['to']));
                        $value['to'] = $this->timezone->convertConfigTimeToUtc($value['to']);
                    }
                    $collection->addFieldToFilter('order_history.' . $key, $value);

                }
            }
        }

        return $collection;
    }

    /**
     * Retrieve product ids
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $collection
     * @return array
     */
    public function retrieveUsedProductList($collection)
    {
        $list = [];
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $cloneSelect = clone $collection->getSelect();

        $result = $cloneSelect
            ->joinLeft(
                ['soi' => 'sales_order_item'],
                'soi.order_id = main_table.entity_id',
                [
                    'type' => 'soi.product_type',
                    'soi.item_id',
                    'soi.parent_item_id'
                ]
            )->joinInner(
                ['cpe' => 'catalog_product_entity'],
                'cpe.entity_id = soi.product_id',
                [
                    'product_id' => 'cpe.entity_id'
                ]
            )->group(
                'cpe.entity_id'
            )->query(
                Zend_Db::FETCH_ASSOC
            )->fetchAll();

        foreach ($result as $item) {
            $list[$item['product_id']] = $item;
        }

        return $list;
    }

    /**
     * Retrieve product attribute values
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param bool $isBbu
     * @return array
     */
    public function retrieveProductAttributeValues($collection, $isBbu = false)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $intSelect = clone $collection->getSelect();

        if ($isBbu) {
            $varcharSelect = clone $collection->getSelect();
        }

        $intAttributes = $intSelect
            ->joinLeft(
                ['cpei' => 'catalog_product_entity_int'],
                'cpei.row_id = e.row_id',
                []
            )->joinInner(
                ['eav' => 'eav_attribute'],
                'eav.attribute_id = cpei.attribute_id',
                [
                    'attribute_code' => 'eav.attribute_code'
                ]
            )->joinLeft(
                [ 'eaov' => 'eav_attribute_option_value'],
                'eaov.option_id = cpei.value',
                [
                    'value' => 'eaov.value'
                ]
            )->where(
                'eav.attribute_code in (?)',
                $this->getIntEntities()
            )->order(
                'e.entity_id'
            )->query(
                Zend_Db::FETCH_ASSOC
            )->fetchAll();

        $varcharAttributes = [];
        if (isset($varcharSelect)) {
            $varcharAttributes = $varcharSelect
                ->joinLeft(
                    ['cpev' => 'catalog_product_entity_varchar'],
                    'cpev.row_id = e.row_id',
                    [
                        'value' => 'cpev.value'
                    ]
                )->joinInner(
                    ['eav' => 'eav_attribute'],
                    'eav.attribute_id = cpev.attribute_id',
                    [
                        'attribute_code' => 'eav.attribute_code'
                    ]
                )->where(
                    'eav.attribute_code in (?)',
                    $this->getVarcharEntities()
                )->query(
                    Zend_Db::FETCH_ASSOC
                )->fetchAll();
            $select2 = $varcharSelect->__toString();
        }

        $result = array_merge($intAttributes, $varcharAttributes);

        return $result;
    }

    /**
     * Get row total list
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $collection
     * @param array $usedProductIds
     * @return array
     */
    public function getRowTotalList($collection, $usedProductIds)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $cloneSelect = clone $collection->getSelect();

        $result = $cloneSelect
            ->joinLeft(
                ['soi' => 'sales_order_item'],
                'soi.order_id = main_table.entity_id',
                [
                    'product_id' => 'soi.product_id',
                    'row_total' => 'SUM(soi.row_total)'
                ]
            )->where(
                'soi.product_id in (?)',
                $usedProductIds
            )->group(
                'soi.product_id'
            )->query(
                Zend_Db::FETCH_ASSOC
            )->fetchAll();

        return $result;
    }

    /**
     * Get refunded total list
     *
     * @param OrderCollection $collection
     * @param array $usedProductIds
     * @return array
     */
    public function getRefundedTotalList($collection, $usedProductIds)
    {
        /** @var OrderCollection $collection */
        $cloneSelect = clone $collection->getSelect();

        $result = $cloneSelect
            ->joinLeft(
                ['soi' => 'sales_order_item'],
                'soi.order_id = main_table.entity_id',
                [
                    'product_id' => 'soi.product_id',
                    'amount_refunded' => 'SUM(soi.amount_refunded)'
                ]
            )->where(
                'soi.product_id in (?)',
                $usedProductIds
            )->where(
                'amount_refunded > 0'
            )->group(
                'soi.product_id'
            )->query(
                Zend_Db::FETCH_ASSOC
            )->fetchAll();

        return $result;
    }

    /**
     * Get qty list
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $collection
     * @param array $usedProductIds
     * @return array
     */
    public function getQtyList($collection, $usedProductIds)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $cloneSelect = clone $collection->getSelect();

        $result = $cloneSelect
            ->joinLeft(
                ['soi' => 'sales_order_item'],
                'soi.order_id = main_table.entity_id',
                [
                    'product_id' => 'soi.product_id',
                    'qty_ordered' => 'SUM(soi.qty_ordered)'
                ]
            )->where(
                'soi.product_id in (?)',
                $usedProductIds
            )->group(
                'soi.product_id'
            )->query(
                Zend_Db::FETCH_ASSOC
            )->fetchAll();

        return $result;
    }

    /**
     * Get refunded qty list
     *
     * @param OrderCollection $collection
     * @param array $usedProductIds
     * @return array
     */
    public function getRefundedQtyList($collection, $usedProductIds)
    {
        /** @var OrderCollection $collection */
        $cloneSelect = clone $collection->getSelect();

        $result = $cloneSelect
            ->joinLeft(
                ['soi' => 'sales_order_item'],
                'soi.order_id = main_table.entity_id',
                [
                    'product_id' => 'soi.product_id',
                    'qty_refunded' => 'SUM(soi.qty_refunded)'
                ]
            )->where(
                'soi.product_id in (?)',
                $usedProductIds
            )->where(
                'qty_refunded > 0'
            )->group(
                'soi.product_id'
            )->query(
                Zend_Db::FETCH_ASSOC
            )->fetchAll();

        return $result;
    }
}
