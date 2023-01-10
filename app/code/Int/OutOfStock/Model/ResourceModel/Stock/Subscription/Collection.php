<?php
/**
 * @author Indusnet Team
 * @package Int_OutOfStock
 */


namespace Int\OutOfStock\Model\ResourceModel\Stock\Subscription;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\ProductAlert\Model\ResourceModel\Stock\Collection as StockCollection;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 */
class Collection extends \Amasty\Xnotif\Model\ResourceModel\Stock\Subscription\Collection
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var string
     */
    protected $productIdLink;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory,$logger,$fetchStrategy,
                            $eventManager,$attributeRepository,$productResource,
                            $connection);
        $this->attributeRepository = $attributeRepository;
        $this->productIdLink = $productResource->getLinkField();
    }

   

    /**
     * @return $this
     */
    public function _renderFiltersBefore()
    {
       
        $nameAttribute = $this->attributeRepository->get(Product::ENTITY, 'name');
        $productVarcharTable = $this->getTable('catalog_product_entity_varchar');

        $this->getSelect()
            ->join(
                ['product' => $this->getTable('catalog_product_entity')],
                //sprintf('product.%s = main_table.product_id', $this->productIdLink),
                'product.entity_id = main_table.product_id',
                ['product_sku' => 'sku']
            )
            ->joinLeft(
                ['customer' => $this->getTable('customer_entity')],
                'customer.entity_id = main_table.customer_id',
                ['last_name' => 'lastname', 'first_name' => 'firstname']
            )
            ->joinLeft(
                ['product_name_by_store' => $productVarcharTable],
                sprintf('product.%s = product_name_by_store.%s', $this->productIdLink, $this->productIdLink)
                . ' AND product_name_by_store.attribute_id = '
                . $nameAttribute->getAttributeId() . ' AND ' . $this->getStoreIdColumn()
                . ' = product_name_by_store.store_id'
            )
            ->joinLeft(
                ['product_name_default' => $productVarcharTable],
                sprintf('product.%s = product_name_default.%s', $this->productIdLink, $this->productIdLink)
                . ' AND product_name_default.attribute_id = '
                . $nameAttribute->getAttributeId() . ' AND product_name_default.store_id = 0'
            );

        $this->updateCustomerFields();

        //parent::_renderFiltersBefore();

        return $this;
    }

    /**
     * Check if field exist in alert table; else get from customer table
     */
    private function updateCustomerFields()
    {
        $columnsPart = $this->getSelect()->getPart('columns');

        $email = new \Zend_Db_Expr('IF (main_table.email IS NOT NULL, main_table.email, customer.email)');
        $productName = new \Zend_Db_Expr(
            'IF (product_name_by_store.value IS NOT NULL, product_name_by_store.value, product_name_default.value)'
        );

        $columnsPart[] = [
            'main_table',
            $this->getStoreIdColumn(),
            'store_id'
        ];
        $columnsPart[] = [
            'main_table',
            $email,
            'email'
        ];
        $columnsPart[] = [
            'main_table',
            $productName,
            'product_name'
        ];

        $this->getSelect()->setPart('columns', $columnsPart);
    }

    /**
     * @return \Zend_Db_Expr
     */
    private function getStoreIdColumn()
    {
        return new \Zend_Db_Expr('IF (main_table.store_id IS NOT NULL, main_table.store_id, customer.store_id)');
    }

    /**
     * @param string $date
     *
     * @return string
     */
    
}
