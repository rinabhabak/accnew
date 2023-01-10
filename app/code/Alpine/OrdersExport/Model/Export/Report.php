<?php
/**
 * Alpine_OrdersExport
 *
 * @category    Alpine
 * @package     Alpine_OrdersExport
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 * @author      Alex Didenko <alex.didenko@alpineinc.com>
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 * @author      Evgeniy Derevyanko <evgeniy.derevyanko@alpineinc.com>
 * @author      Anton Smolenov <anton.smolenov@alpineinc.com>
 */

namespace Alpine\OrdersExport\Model\Export;

use Alpine\OrdersExport\Model\Export\ResourceModel\Collection as ResourceModel;
use Alpine\OrdersExport\Helper\Data;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Alpine\OrdersExport\Model\Export\Report
 *
 * @category    Alpine
 * @package     Alpine_OrdersExport
 */
class Report
{
    /**
     * Resource model
     *
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * Data helper
     *
     * @var Data
     */
    protected $helper;

    /**
     * Csv
     *
     * @var Csv
     */
    protected $csv;

    /**
     * DirectoryList
     *
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * Price helper
     *
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * Order Collection Factory
     *
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * Product Collection Factory
     *
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Filters
     *
     * @var array
     */
    protected $filters;

    /**
     * Constructor
     *
     * @param ResourceModel $resourceModel
     * @param Data $helper
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Csv $csv
     * @param DirectoryList $directoryList
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        ResourceModel $resourceModel,
        Data $helper,
        OrderCollectionFactory $orderCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        Csv $csv,
        DirectoryList $directoryList,
        PriceHelper $priceHelper
    ) {
        $this->resourceModel = $resourceModel;
        $this->helper = $helper;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->csv = $csv;
        $this->directoryList = $directoryList;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Get content
     *
     * @param string $fileName
     * @param array $headers
     * @param array $filters
     * @param bool $isBbu
     * @return array
     */
    public function getContent($fileName, $headers, $filters = [], $isBbu = false)
    {
        $orderCollection = $this->orderCollectionFactory->create()
            ->addFieldToSelect('entity_id');
    $orderCollection->addFieldToFilter("status", array("in"=>array('complete','processing')));
        $this->resourceModel->applyFilters($orderCollection, $filters);
        $usedProductList = $this->resourceModel->retrieveUsedProductList($orderCollection);
        $usedSimpleIds = $this->retrieveSimpleProductIds($usedProductList);
        $productCollection = $this->productCollectionFactory->create()
            ->addFieldToSelect('entity_id')
            ->addFieldToFilter('entity_id', ['in' => $usedSimpleIds]);

        $productAttributesList = $this->resourceModel->retrieveProductAttributeValues($productCollection, $isBbu);
        $usedAllProductIds = $this->retrieveAllProductIds($usedProductList);
        $rowTotalList = $this->helper->setValueAsGlobalKey(
            $this->resourceModel->getRowTotalList($orderCollection, $usedAllProductIds),
            'product_id',
            'row_total'
        );

        $refundedTotalList = $this->helper->setValueAsGlobalKey(
            $this->resourceModel->getRefundedTotalList($orderCollection, $usedAllProductIds),
            'product_id',
            'amount_refunded'
        );

        $qtyList = $this->helper->setValueAsGlobalKey(
            $this->resourceModel->getQtyList($orderCollection, $usedSimpleIds),
            'product_id',
            'qty_ordered'
        );

        $qtyRefundedList = $this->helper->setValueAsGlobalKey(
            $this->resourceModel->getRefundedQtyList($orderCollection, $usedSimpleIds),
            'product_id',
            'qty_refunded'
        );

        $mappedProducts = $this->mapSimpleToConfigurable($usedProductList);
        $processedData = $this->processData(
            $headers,
            $productAttributesList,
            $rowTotalList,
            $refundedTotalList,
            $qtyList,
            $qtyRefundedList,
            $mappedProducts
        );
        array_unshift($processedData, $headers);
        $filePath = $this->writeToCsv($fileName, $processedData);

        return [
            'type' => 'filename',
            'value' => $filePath,
            'rm' => true,
        ];
    }

    /**
     * Process data
     *
     * @param array $headers
     * @param array $productAttributesList
     * @param array $rowTotal
     * @param array $refundedTotal
     * @param array $qtyOrdered
     * @param array $qtyRefunded
     * @param array $mappedProducts
     * @return array
     */
    protected function processData(
        $headers,
        $productAttributesList,
        $rowTotal,
        $refundedTotal,
        $qtyOrdered = [],
        $qtyRefunded = [],
        $mappedProducts = []
    ) {
        $data = [];
        $dataMask = $this->helper->unsetValues($headers);
        $refundedValue = 0;

        /** @var \Magento\Framework\DataObject $item */
        foreach ($productAttributesList as $attribute) {
            $curProdId = $attribute['entity_id'];

            if (isset($dataMask['bbu']) && (!$attribute['value'] || !$qtyOrdered[$curProdId])) {
                continue;
            }

            if (!isset($data[$curProdId])) {
                if (isset($mappedProducts[$curProdId])) {
                    $rowValue = $rowTotal[$mappedProducts[$curProdId]];
                    if (isset($rowTotal[$curProdId])) {
                        $rowValue += $rowTotal[$curProdId];
                    }

                    $refundedValue = $refundedTotal[$mappedProducts[$curProdId]] ?? 0;
                    if (isset($refundedTotal[$curProdId])) {
                        $refundedValue += $refundedTotal[$curProdId];
                    }
                } else {
                    $rowValue = $rowTotal[$curProdId];
                    $refundedValue = $refundedTotal[$curProdId] ?? 0;
                }

                $tempClearRevenue = $this->priceHelper->currency(
                    $rowValue,
                    false,
                    false
                );

                if (!$tempClearRevenue) {
                    continue;
                }

                $data[$curProdId] = $dataMask;

                if (isset($data[$curProdId]['revenue'])) {
                    $data[$curProdId]['revenue'] = $rowValue;
                }
            }

            if (isset($headers[$attribute['attribute_code']])) {
                $data[$curProdId][$attribute['attribute_code']] = $attribute['value'];
            }

            if (isset($data[$curProdId]['bbu'])) {
                $data[$curProdId]['bbu'] = $attribute['value'] * $qtyOrdered[$curProdId];

                if (isset($qtyRefunded[$curProdId])) {
                    $data[$curProdId . '_refunded'] = $data[$curProdId];
                    $data[$curProdId . '_refunded']['bbu'] = $attribute['value']
                        * $qtyRefunded[$curProdId] * (-1);
                }
            }

            if (isset($data[$curProdId]['revenue']) && $refundedValue) {
                $data[$curProdId . '_refunded'] = $data[$curProdId];
                $data[$curProdId . '_refunded']['revenue'] = $refundedValue * (-1);
            }
        }
        $data = $this->mergeBySlideSeries($data);
        foreach ($data as &$row) {
            if (isset($row['revenue'])) {
                $row['revenue'] = $this->priceHelper->currency(
                    $row['revenue'],
                    true,
                    false
                );
            }
        }

        return $data;
    }

    /**
     * Write data to csv file
     *
     * @param string $fileName
     * @param array $data
     * @return string
     */
    protected function writeToCsv($fileName, $data)
    {
        $fileDirectoryPath = $this->directoryList
            ->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);

        $filePath = $fileDirectoryPath . DIRECTORY_SEPARATOR . $fileName;
        $this->csv->saveData($filePath, $data);

        return $filePath;
    }

    /**
     * Retrieve simple products' id from source list
     *
     * @param array $sourceList
     * @return array
     */
    protected function retrieveSimpleProductIds($sourceList)
    {
        $simpleIds = [];

        foreach ($sourceList as $product) {
            if ($product['type'] === 'simple') {
                $simpleIds[] = $product['product_id'];
            }
        }

        return $simpleIds;
    }

    /**
     * Retrieve all products' id from source list
     *
     * @param array $sourceList
     * @return array
     */
    protected function retrieveAllProductIds($sourceList)
    {
        $allIds = [];

        foreach ($sourceList as $product) {
            $allIds[] = $product['product_id'];
        }

        return $allIds;
    }

    /**
     * Prepares array where simple id is mapped to configurable id
     *
     * @param $sourceList
     */
    protected function mapSimpleToConfigurable($sourceList)
    {
        $mapping = [];
        $parents = [];
        $buffer = [];

        foreach ($sourceList as $product) {
            if (!isset($mapping[$product['product_id']])) {
                if ($product['type'] === 'simple') {
                    if (isset($parents[$product['parent_item_id']])) {
                        $mapping[$product['product_id']] = $parents[$product['parent_item_id']];
                    } else {
                        $buffer[$product['parent_item_id']] = $product['product_id'];
                    }
                }
                if ($product['type'] === 'configurable') {
                    $parents[$product['item_id']] = $product['product_id'];
                    if (isset($buffer[$product['item_id']])) {
                        $mapping[$buffer[$product['item_id']]] = $product['product_id'];
                        unset($buffer[$product['item_id']]);
                    }
                }
            }
        }

        return $mapping;
    }

    /**
     * Merge array by Slide Series
     *
     * @param array $data
     * @return array
     */
    protected function mergeBySlideSeries($data)
    {
        $mergedData = [];
        foreach ($data as $key => $row) {
            $newKey = $row['slide_series'] . (substr_count($key , '_refunded') ? '_refunded' : '');
            $totalCost = $mergedData[$newKey]['revenue'] ?? 0;
            $totalBbu = $mergedData[$newKey]['bbu'] ?? 0;
            $mergedData[$newKey] = $row;
            if (isset($row['revenue'])) {
                $mergedData[$newKey]['revenue'] += $totalCost;
            }
            if (isset($row['bbu'])) {
                $mergedData[$newKey]['bbu'] += $totalBbu;
            }
        }

        return $mergedData;
    }
}
