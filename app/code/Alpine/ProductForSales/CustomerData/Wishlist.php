<?php
/**
 * Alpine_ProductForSales
 *
 * @category    Alpine
 * @package     Alpine_ProductForSales
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\ProductForSales\CustomerData;

use Magento\Wishlist\CustomerData\Wishlist as BaseWishlist;
use Magento\Wishlist\Model\Item;
use Magento\Framework\Pricing\Render;

/**
 * Alpine\ProductForSales\CustomerData\Wishlist
 *
 * @category    Alpine
 * @package     Alpine_ProductForSales
 */
class Wishlist extends BaseWishlist
{
    /**
     * Retrieve wishlist item data
     *
     * @param Item $wishlistItem
     * @return array
     */
    protected function getItemData(Item $wishlistItem)
    {
        $product = $wishlistItem->getProduct();
        return [
            'image' => $this->getImageData($product),
            'product_url' => $this->wishlistHelper->getProductUrl($wishlistItem),
            'product_name' => $product->getName(),
            'product_price' => $this->block->getProductPriceHtml(
                $product,
                'wishlist_configured_price',
                Render::ZONE_ITEM_LIST,
                ['item' => $wishlistItem]
            ),
            'product_is_saleable_and_visible' => $product->isSaleable()
                && $product->isVisibleInSiteVisibility()
                && $product->getProductForSales(),
            'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
            'add_to_cart_params' => $this->wishlistHelper->getAddToCartParams($wishlistItem, true),
            'delete_item_params' => $this->wishlistHelper->getRemoveParams($wishlistItem, true),
        ];
    }
}
