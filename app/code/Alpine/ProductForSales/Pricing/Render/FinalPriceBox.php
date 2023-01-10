<?php
/**
 * Alpine_ProductForSales
 *
 * @category    Alpine
 * @package     Alpine_ProductForSales
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\ProductForSales\Pricing\Render;

/**
 * Class for final_price rendering
 *
 * @category    Alpine
 * @package     Alpine_ProductForSales
 */
class FinalPriceBox
{
    /**
     * Remove final price box if product is not for sales
     *
     * @param $subject
     * @param callable $proceed
     * @return string
     */
    function aroundToHtml($subject, callable $proceed)
    {
        if ($product = $subject->getSaleableItem()) {
            if (!$product->getProductForSales()) {
                return '';
            }
        };
        return $proceed();
    }
}