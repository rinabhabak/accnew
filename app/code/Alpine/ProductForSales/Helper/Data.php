<?php
/**
 * Alpine_ProductForSales
 *
 * @category    Alpine
 * @package     Alpine_ProductForSales
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\ProductForSales\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Wishlist\Model\Wishlist;

/**
 * Alpine\ProductForSales\Helper\Data
 *
 * @category    Alpine
 * @package     Alpine_ProductForSales
 */
class Data extends AbstractHelper
{
    /**
     * Is display 'Add All to Cart'
     *
     * @param Wishlist $wishlist
     * @return boolean
     */
    public function isForSales(Wishlist $wishlist)
    {
        foreach ($wishlist->getItemCollection() as $item) {
            if (!$item->getProduct()->getProductForSales()) {
                return false;
            }
        }
        
        return true;
    }
}
