<?php
/**
 * Alpine_Catalog
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Aleksandr Mikhailov (aleksandr.mikhailov@alpineinc.com)
 */

namespace Alpine\Catalog\Plugin\Model\Product\Type;

use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProduct;

/**
 * Class \Alpine\Catalog\Plugin\Model\Product\Type\Grouped
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 */
class Grouped
{
    /**
     * Add 'thumbnail' field to select of associated products
     *
     * @param GroupedProduct $subject
     * @param Collection     $result
     * @return Collection
     */
    public function afterGetAssociatedProductCollection(GroupedProduct $subject, Collection $result)
    {
        $result->addFieldToSelect(['thumbnail']);

        return $result;
    }
}