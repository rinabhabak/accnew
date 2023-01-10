<?php
/**
 * Alpine_OrdersExport Export Model
 *
 * @category    Alpine
 * @package     Alpine_OrdersExport
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\OrdersExport\Model\Export;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Zend_Db;

/**
 * Alpine_OrdersExport Export Model
 *
 * @category    Alpine
 * @package     Alpine_OrdersExport
 */
class Index
{
    /**
     * Order Collection Factory
     *
     * @var CollectionFactory $orderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * Filters to search for orders
     *
     * @var array $filters
     */
    protected $filters;

    /**
     * File Name of the report
     *
     * @var string $fileName
     */
    public $fileName;

    /**
     * Int entities
     *
     * @var string $intEntities
     */
    protected $intEntities;

    /**
     * Varchar entities
     *
     * @var string $varcharEntities
     */
    protected $varcharEntities;

    /**
     * Revenue entity
     *
     * @var string $revenueEntity
     */
    protected $revenueEntity;

    /**
     * Headers codes
     *
     * @var string $headersCodes
     */
    protected $headersCodes;

    /**
     * Headers Labels
     *
     * @var string $headersLabels
     */
    protected $headersLabels;

    /**
     * Date Format From which to convert date filter
     *
     * @var string $dateFromFormat
     */
    protected $dateFromFormat;

    /**
     * Date Format To which to convert date filter
     *
     * @var string $dateToFormat
     */
    protected $dateToFormat;

    /**
     * CR LF
     *
     * @var string $rn
     */
    protected $rn;

    /**
     * Field separator
     *
     * @var string $fieldSeparator
     */
    protected $fieldSeparator;

    /**
     * Index constructor
     *
     * @param CollectionFactory $orderCollectionFactory
     */
    public function __construct(CollectionFactory $orderCollectionFactory)
    {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->configure();
    }

    /**
     * Configure report view with the fields you need
     *
     * @param string $fileName
     * @param null $filters
     * @param string $intEntities int entities
     * @param string $varcharEntities varchar entities
     * @param string $revenueEntity revenue entity
     * @param string $revenueLabel revenue label
     * @param string $dateFromFormat
     * @param string $dateToFormat
     * @param string $rn
     */
    public function configure(
        $fileName = 'ExportReport.csv',
        $filters = null,
        $intEntities = "'slide_series','account_number','cost_center','company_number','subaccount','project_number'",
        $varcharEntities = "'bbu'",
        $revenueEntity = "'revenue'",
        $revenueLabel = 'Revenue',
        $dateFromFormat = 'm/d/Y',
        $dateToFormat = 'Y-m-d',
        $rn = "\r\n",
        $fieldSeparator = ';'
    )
    {
        /** @var string $fileName */
        $this->setFileName($fileName);

        /** @var array $filters */
        $this->filters = $filters;

        // Slide Series,Account Number,Cost Center,Company Number,Subaccount,Project Number,BBU,Revenue

        /** @var string $intEntities */
        $this->intEntities = $intEntities;

        /** @var string $varcharEntities */
        $this->varcharEntities = $varcharEntities;

        /** @var string $revenueEntity */
        $this->revenueEntity = $revenueEntity;

        $this->headersCodes = explode(',', str_ireplace(
            "'", '', $this->intEntities . ',' . $this->varcharEntities . ',' . $this->revenueEntity));

        // 'revenue' it is 'order_item.price'
        /** @var string $revenueLabel */
        $this->headersLabels[str_ireplace("'", '', $this->revenueEntity)] = $revenueLabel;

        /** @var string $fromFormat */
        $this->dateFromFormat = $dateFromFormat;

        /** @var string $toFormat */
        $this->dateToFormat = $dateToFormat;

        /** @var string $rn */
        $this->rn = $rn;

        /** @var string $rn */
        $this->fieldSeparator = $fieldSeparator;
    }

    /**
     * Getter for $fileName
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Setter for $fileName
     *
     * @param string $fileName
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Convert date $date from format $fromFormat to format $toFormat
     *
     * @param $date
     * @return false|string
     */
    public function convertDate($date)
    {
        /** @var object $obj */
        $obj = date_create_from_format($this->dateFromFormat, $date);
        if ($obj === FALSE) {
            return $date;
        }
        return date_format($obj, $this->dateToFormat);
    }

    /**
     * Apply Filter values to Collection
     *
     * @param $filters
     * @param $items
     * @return mixed
     */
    public function applyFilter($filters, $items)
    {
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'placeholder':
                    $key = '';
                    break;
                case 'increment_id':
                    $key = 'entity_id';
                    break;
                case 'created_at':
                    $value = ['from' => $this->convertDate($value['from']), 'to' => $this->convertDate($value['to'])];
                    break;
            }
            if ($key != '' && $value != '') {
                $items->addFieldToFilter('main_table.' . $key, $value);
            }
        }
        return $items;
    }

    /**
     * Process item
     *
     * @param $result
     * @param $item
     * @param $value
     * @param null $revenue
     * @return mixed
     */
    public function processItem($result, $item, $value, $revenue = null)
    {
        $orderId = $item['order_id'];
        $productId = $item['product_id'];

        $result[$orderId . '-' . $productId][$item['attribute_code']] = $value;

        if ($revenue) {
            $result[$orderId . '-' . $productId]['revenue'] = '$' . number_format($revenue, 2);
        }
        return $result;
    }

    /**
     * Process headers labels
     *
     * @param $item
     */
    public function processHeadersLabels($item)
    {
        $this->headersLabels[$item['attribute_code']] = $item['attribute_label'];
    }

    /**
     * Add First SQL
     *
     * @param $items
     * @return mixed
     */
    public function AddFirstSQL()
    {
        $items = $this->applyFilter($this->filters, $this->orderCollectionFactory->create());
        //$sql = $items->getSelect()->assemble(); echo $sql.'<HR>'; //die($sql); // look for plain SQL
        $items
            ->addFieldToSelect('entity_id', 'order_id')
            ->getSelect()
            ->joinLeft(['order_item' => 'sales_order_item'], 'main_table.entity_id = order_item.order_id',
                ['product_id' => 'order_item.product_id', 'revenue' => 'order_item.price', 'qty' => 'order_item.qty_ordered'])
            ->joinLeft(['ce' => 'catalog_product_entity'], 'ce.entity_id = order_item.product_id', null);
        return $items;
    }

    /**
     * Join eav_attribute
     *
     * @param $items
     * @return mixed
     */
    public function joinEA($items, $alias)
    {
        $items
            ->getSelect()
            ->joinLeft(['ea' => 'eav_attribute'], "ea.attribute_id = $alias.attribute_id",
                ['attribute_code' => 'ea.attribute_code', 'attribute_label' => 'ea.frontend_label']);
        return $items;
    }

    /**
     * Execute SQL for report
     *
     * @return mixed
     */
    public function executeSQL()
    {
        // SQL for items
        $items = $this->AddFirstSQL();
        $items
            ->getSelect()
            ->joinLeft(['ce_int' => 'catalog_product_entity_int'],
                'ce_int.row_id = ce.row_id', null);
        $items = $this->joinEA($items, 'ce_int');
        $items
            ->getSelect()
            ->joinLeft(['eaov' => 'eav_attribute_option_value'], 'eaov.option_id = ce_int.value', ['value' => 'eaov.value'])
            ->where("ea.attribute_code in ($this->intEntities)")
            ->order('ea.attribute_code')
            ->order('eaov.value')
            ->order('order_item.product_id');

        //$sql = $items->getSelect()->assemble(); echo $sql . '<HR>'; //die($sql); // look for plain SQL
        $items = $items->getSelect()->query(Zend_Db::FETCH_ASSOC);
        return $items;
    }

    /**
     * Execute SQL for BBU report
     *
     * @return mixed
     */
    public function executeSQLBBU()
    {
        // SQL for itemsBBU
        $itemsBBU = $this->AddFirstSQL();
        $itemsBBU
            ->getSelect()
            ->joinLeft(['ce_varchar' => 'catalog_product_entity_varchar'],
                'ce_varchar.row_id = ce.row_id', ['value' => 'ce_varchar.value']);
        $itemsBBU = $this->joinEA($itemsBBU, 'ce_varchar');
        $itemsBBU
            ->getSelect()
            ->where("ea.attribute_code in ($this->varcharEntities)");

        //$sql = $itemsBBU->getSelect()->assemble(); echo $sql . '<HR>'; //die($sql); // look for plain SQL

        $itemsBBU = $itemsBBU->getSelect()->query(Zend_Db::FETCH_ASSOC);
        //$items = $items->union([$itemsBBU->assemble()], $items::SQL_UNION_ALL);
        return $itemsBBU;
    }

    /**
     * Items special processing
     *
     * @param $items
     * @return array|mixed
     */
    public function ProcessItems($items)
    {
        $result = [];
        foreach ($items as $item) {
            $this->processHeadersLabels($item);
            $result = $this->processItem($result, $item, $item['value'], $item['revenue']);
        }
        return $result;
    }

    /**
     * BBU Items special processing
     *
     * @param $itemsBBU
     * @param $result
     * @return mixed
     */
    public function ProcessItemsBBU($itemsBBU, $result)
    {
        foreach ($itemsBBU as $item) {
            $this->processHeadersLabels($item);
            // 5. BBU column is a summation of all BBU rate of each product * qty purchased.
            // This means that, IF a group has a quantity of 3 units sold for one product with a BBU of 4, THEN the total BBU for that group is 12.
            $result = $this->processItem($result, $item, $item['value'] * $item['qty']);
        }
        return $result;
    }

    /**
     * Get first line of the content, like this:
     * Slide Series;Account Number;Cost Center;Company Number;Subaccount;Project Number;BBU;Revenue;
     *
     * @return string
     */
    public function getFirstLine()
    {
        $firstLine = '';
        foreach ($this->headersCodes as $key) {
            if (array_key_exists($key, $this->headersLabels)) {
                $firstLine .= $this->headersLabels[$key] . $this->fieldSeparator;
            }
        }
        return $firstLine;
    }

    /**
     * Get report content from $result
     *
     * @param $result
     * @return string
     */
    public function getContent($result)
    {
        $content = $this->getFirstLine() . $this->rn;

        foreach ($result as $key => $row) {
            $line = '';
            foreach ($this->headersCodes as $header) {
                if (array_key_exists($header, $row)) {
                    $line .= $row[$header] . $this->fieldSeparator;
                }
            }
            $content .= $line . $this->rn;
        }
        return $content;
    }

    /**
     * Execute Model action
     *
     * @return string
     */
    public function execute()
    {
        $items = $this->executeSQL();
        $result = $this->ProcessItems($items);
        return $this->getContent($result);
    }

    /**
     * Execute Model action for report with BBU field
     *
     * @return string
     */
    public function executeBBU()
    {
        $items = $this->executeSQL();
        $itemsBBU = $this->executeSQLBBU(); // Only for BBU

        $result = $this->ProcessItems($items);
        $result = $this->ProcessItemsBBU($itemsBBU, $result);  // Only for BBU

        return $this->getContent($result);
    }
}