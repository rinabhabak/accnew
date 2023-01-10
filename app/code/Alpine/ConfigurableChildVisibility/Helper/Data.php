<?php
/**
 * Alpine_ConfigurableChildVisibility
 *
 * @category    Alpine
 * @package     Alpine_Accuride
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Derevyanko Evgeniy (evgeniy.derevyanko@alpineinc.com)
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */

namespace Alpine\ConfigurableChildVisibility\Helper;

use Magento\Catalog\Model\Product;
use \Magento\CatalogInventory\Api\StockStateInterface;
/**
 * Class Data
 * Helper class for getting options
 */
class Data extends \Magento\ConfigurableProduct\Helper\Data
{
    /**
     * Catalog Image Helper
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    
    /**
     * @var StockStateInterface 
     */
    protected $stockItem;
    
    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param StockStateInterface $stockItem
     */   
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        StockStateInterface $stockItem
    ) {
        $this->stockItem = $stockItem;
        parent::__construct($imageHelper);
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param \Magento\Catalog\Model\Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $allowAttributes = $this->getAllowAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            $productStock = $this->stockItem->getStockQty($productId, $product->getStore()->getWebsiteId());
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                $options[$productAttributeId][$attributeValue][] = $productId;
                $options['index'][$productId][$productAttributeId] = $attributeValue;
                $options['count'][$productAttributeId][$attributeValue][$productId] = $productStock;
            }
        }
        return $options;
    }
}
