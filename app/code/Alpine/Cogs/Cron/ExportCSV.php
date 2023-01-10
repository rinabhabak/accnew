<?php
/**
 * Create export files with last orders and returns data / Alpine_Cogs
 *
 * @category    Alpine
 * @package     Alpine_Cogs
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Evgeniy Derevyanko (evgeniy.derevyanko@alpineinc.com)
 */

namespace Alpine\Cogs\Cron;

use \Psr\Log\LoggerInterface;
use \Magento\Framework\File\Csv;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Filesystem;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\Api\SortOrderBuilder;
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Rma\Model\ResourceModel\ItemFactory;
use \Magento\Rma\Model\RmaFactory;
use \Magento\Sales\Model\OrderFactory;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Alpine\Cogs\Model\Filesystem\Io\Ftp;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \Magento\Framework\App\ResourceConnection;
use \Magento\Store\Model\StoreManagerInterface;

/**
 * ExportCSV - Export to .csv class
 *
 * @category    Alpine
 * @package     Alpine_Cogs
 */
class ExportCSV 
{
    /**
     * default export folder
     * 
     * @var string
     */
    const EXPORT_FOLDER = '/export';
    
    /**
     * logger interface
     * 
     * @var LoggerInterface 
     */
    protected $logger;
    
    /**
     * filesystem
     * 
     * @var Filesystem 
     */
    protected $filesystem;
    
    /**
     * directory list
     * 
     * @var DirectoryList 
     */
    protected $directoryList;
    
    /**
     * csv processor
     * 
     * @var Csv 
     */
    protected $csvProcessor;
    
    /**
     * RMA factory
     * 
     * @var RmaFactory 
     */
    protected $rmaFactory;
    
    /**
     * Order factory
     * 
     * @var OrderFactory 
     */
    protected $orderFactory;
    
    /**
     * Search Criteria Builder
     *
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    
    /**
     * Sort Order Builder
     *
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;
    
    /**
     * Order Repository Interface
     *
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * Product factory
     * 
     * @var ProductFactory 
     */
    protected $product;
    
    /**
     * RMA items factory
     *
     * @var ItemFactory 
     */
    protected $itemFactory;
    
    /**
     * Product Repository
     * 
     * @var ProductRepositoryInterface 
     */
    protected $productRepository;
    
    /**
     * list of exported order id's
     *
     * @var array
     */
    private $orderIds;
    
    /**
     * list of exported rma id's
     *
     * @var array
     */
    private $rmaIds;
    
    /**
     * FTP helper
     * 
     * @var Ftp
     */
    protected $ftp;
    
    /**
     * Timezone interface 
     *
     * @var TimezoneInterface 
     */
    protected $dateTime;
    
    /**
     * @var CollectionFactory
     */
    protected $orderCollection;

    protected $resourceConnection;

    protected $storeManagerInterface;
    

    const UOM = [
        'Each'  => 'EA',
        'Pairs' => 'PR',
        'Pair'  => 'PR',
        'Kit'   => 'KT'
    ];
    
    /**
     * Export to csv class constructo
     * 
     * @param LoggerInterface $logger
     * @param Csv $csvProcessor
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param RmaFactory $rmaFactory
     * @param OrderFactory $orderFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductFactory $product
     * @param ProductRepositoryInterface $productRepository
     * @param ItemFactory $itemFactory
     * @param Ftp $ftp
     * @param TimezoneInterface $dateTime
     * @param OrderFactory $orderCollection
     */
    public function __construct(
        LoggerInterface $logger,
        Csv $csvProcessor,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        RmaFactory $rmaFactory,
        OrderFactory $orderFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        OrderRepositoryInterface $orderRepository,
        ProductFactory $product,
        ProductRepositoryInterface $productRepository,
        ItemFactory $itemFactory,
        Ftp $ftp,
        TimezoneInterface $dateTime,
        OrderFactory $orderCollection,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManagerInterface

    ) {
        $this->logger                = $logger;
        $this->filesystem            = $filesystem;  
        $this->directoryList         = $directoryList;
        $this->csvProcessor          = $csvProcessor;
        $this->rmaFactory            = $rmaFactory;
        $this->orderFactory          = $orderFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository       = $orderRepository;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->product               = $product;
        $this->productRepository     = $productRepository;
        $this->itemFactory           = $itemFactory;
        $this->ftp                   = $ftp;
        $this->dateTime              = $dateTime;
        $this->orderCollection       = $orderCollection;
        $this->resourceConnection    = $resourceConnection;
        $this->storeManagerInterface = $storeManagerInterface;
    }
    
    /**
     * execute method
     */
    public function execute()
    {
        $ordersData = $this->getOrdersData();
        $returnsData = $this->getReturnsData();
        $data = array_merge($ordersData, $returnsData);
        if (empty($data)) {
            return;
        }
        if ($fileName = $this->writeToCsv($data)) {
            $this->setExportedOrders();
            $this->setExportedReturns();
        }
        if ($this->ftp->export([$fileName])) {
            $this->logger->info('FTP: SUCCESS');
        } else {
            $this->logger->warning('FTP: FAIL');
        }
    }
    
    /**
     * get data from completed orders
     * 
     * @return array
     */
    protected function getOrdersData()
    {        
        $ordersList = $this->orderCollection->create()->getCollection()
            ->addFieldToFilter('is_exported', 0)
            ->addFieldToFilter('status','complete');
        
        $this->orderIds = [];
        
        $result = [];
        foreach ($ordersList as $order) {
            $orderId =  substr($order->getIncrementId(), -5);
            $index = 1;
            foreach ($order->getItemsCollection() as $item) {
                if ($item->getData('product_type') !== 'simple' && 
                    $item->getData('product_type') !== 'grouped') {
                    continue;
                }
                $this->orderIds[$order->getId()] = $order->getId();
                try {
                    $product  = $this->productRepository->getById($item->getData("product_id"));
                } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->logger->warning('FTP: No such product (order)');
                    continue;
                }
                $temp     = [];
                $temp[]   = $orderId;
                $temp[]   = (string)($index/10 < 1 ? '0'.$index : $index);
                $temp[]   = $product->getAttributeText('cost_center');
                $temp[]   = $product->getMpn();
                $uom = $product->getAttributeText('uom');
                if ($uom) { // remove logger
                    $temp[] = self::UOM[$uom];
                } else {
                    $temp[] = '';
                }
                $temp[]   = (int)$item->getData('qty_ordered');
                $result[] = $temp;
                $index++;
            }
        }
        return $result;
    }
    /**
     * get data from completed returns
     * 
     * @return array
     */
    protected function getReturnsData()
    {
        $rmaCollection = $this->rmaFactory->create()->getCollection()
            ->addFieldToFilter('is_exported', 0);
        $result = [];
        $this->rmaIds = []; 
        foreach ($rmaCollection as $rma) {
            $order = $this->orderRepository->get($rma->getData("order_id"));
            if ($order->getStatus() !== 'complete') continue;
            $rmaItems = $rma->getItemsForDisplay(true);
            $index = 1;
            foreach ($rmaItems as $rmaItem) {
                if (!(int)$rmaItem->getData("qty_approved")) continue;
                $this->rmaIds[$rma->getId()] = $rma->getId();
                try {
                    $product  = $this->productRepository->get($rmaItem->getData("product_sku"));
                } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->logger->warning('FTP: No such product (rma)');
                    continue;
                }
                $temp     = [];
                $temp[]   = substr($rma->getData('order_increment_id'), -5);
                $temp[]   = ($index/10 < 1 ? '0'.$index : $index);
                $temp[]   = $product->getAttributeText('cost_center');
                $temp[]   = $product->getMpn();
                $uom = $product->getAttributeText('uom');
                if ($uom) {
                    $temp[] = self::UOM[$uom];
                } else {
                    $temp[] = '';
                }
                $temp[]   = '-' . (int)$rmaItem->getData("qty_approved");
                $temp[]   = substr($rma->getData('increment_id'), -5);
                $result[] = $temp;
                $index++;
            }
        }
        return $result;
    }
    
    /**
     * write result array to CSV file
     * 
     * @param array $data
     * @return string|bool $filename
     */
    protected function writeToCsv(array $data)
    {
        $date = $this->dateTime->date()->format('m-d-Y');
	$fileDirectoryPath = $this->directoryList->getPath('var') . self::EXPORT_FOLDER;
	$fileName = 'order-export' . $date . '.csv';
        if (!file_exists($fileDirectoryPath)) {
            mkdir($fileDirectoryPath, 0755, true);
        }
	$filePath =  $fileDirectoryPath . '/' . $fileName;
        if ($this->csvProcessor
	    ->setEnclosure('"')
	    ->setDelimiter(',')
	    ->saveData($filePath, $data)) {
            return $fileName;
        } else {
            return false;
        }
    }
    
    /**
     * mark exported orders in database
     */
    protected function setExportedOrders()
    {
        foreach ($this->orderIds as $orderId) {
            $this->orderFactory->create()->load($orderId)->setIsExported(1)->save();
        }
        $this->orderIds = [];
    }
    
    /**
     * mark exported RMA's in database
     */
    protected function setExportedReturns()
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('magento_rma');
        foreach ($this->rmaIds as $rmaId) {
            $sql = "Update " . $tableName . " SET is_exported = '1' where entity_id = ".$rmaId;
            $connection->query($sql);
        }
        $this->rmaIds = [];
    }
}
