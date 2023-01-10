<?php
/* 
 * Alpine_ConfigurableChildVisibility
*
* @category    Alpine
* @package     Alpine_Accuride
* @copyright   Copyright (c) 2018 Alpine Consulting, Inc
* @author      Derevyanko Evgeniy <evgeniy.derevyanko@alpineinc.com>
*/
declare(strict_types=1);

namespace Alpine\ConfigurableChildVisibility\Plugin\Model\ResourceModel\Attribute;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Attribute\InStockOptionSelectBuilder as CoreInStockOptionSelectBuilder;
use Magento\Framework\DB\Select;

/**
 *  In stock plugin class
 */
class InStockOptionSelectBuilder extends CoreInStockOptionSelectBuilder
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * InStockOptionSelectBuilder constructor
     *
     * @param Status $stockStatusResource
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        Status $stockStatusResource,
        StockConfigurationInterface $stockConfiguration
    ) {
        parent::__construct($stockStatusResource, $stockConfiguration);
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Only Add In stock Filter if Show Out Of Stock Products is set to No
     *
     * @param OptionSelectBuilderInterface $subject
     * @param Select $select
     * @return Select
     */
    public function afterGetSelect(
        OptionSelectBuilderInterface $subject,
        Select $select
    ) {
        if (!$this->stockConfiguration->isShowOutOfStock()) {
            return parent::afterGetSelect($subject, $select);
        }
        return $select;
    }
}
