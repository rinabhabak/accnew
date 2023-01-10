<?php
namespace Int\OrderCompleteDate\Plugins\Sales\Order\Grid;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
use Magento\Framework\Registry;
use Magento\Framework\App\ResourceConnection;

class Collection extends SalesOrderGridCollection
{
    private $collection;
    private $registry;
    private $resource;

    public function __construct(
        SalesOrderGridCollection $collection,
        ResourceConnection $resource,
        Registry $registry
    ){
        $this->collection = $collection;
        $this->registry = $registry;
        $this->resource = $resource;
    }

    public function afterGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        $collection,
        $requestName
    ){
        if ($requestName == 'sales_order_grid_data_source') {
            
            $collection->addFilterToMap('created_at', 'main_table.created_at');
            $collection->addFilterToMap('ship_at', 'order_grid.ship_at');
   
            $select = $collection->getSelect(); 
            $sales_order_status_history = $this->resource->getTableName('sales_order_grid');

            $subquery = new \Zend_Db_Expr('(SELECT entity_id AS clone_id, updated_at AS ship_at FROM '. $sales_order_status_history .' where status IN ("complete","closed"))');

            $select->joinLeft( 
                array( 'order_grid' => $subquery ),
                'main_table.entity_id=order_grid.clone_id'
            );
            
        }

        return $collection;
    }
}
