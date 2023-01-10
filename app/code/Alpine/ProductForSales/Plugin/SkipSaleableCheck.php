<?php
/**
 * Alpine_ProductForSales
 *
 * @category    Alpine
 * @package     Alpine_ProductForSales
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Alex Didenko <alex.didenko@alpineinc.com>
 */

namespace Alpine\ProductForSales\Plugin;

use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\App\Request\Http;

/**
 * Alpine\ProductForSales\Plugin\SkipSaleableCheck
 *
 * @category    Alpine
 * @package     Alpine_ProductForSales
 */
class SkipSaleableCheck
{
    /**
     * Product Helper
     *
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * Http Request
     *
     * @var Http
     */
    protected $request;

    /**
     * SkipSaleableCheck constructor
     *
     * @param ProductHelper $productHelper
     * @param Http $request
     */
    public function __construct(
        ProductHelper $productHelper,
        Http $request
    ) {
        $this->productHelper = $productHelper;
        $this->request = $request;
    }

    /**
     * Before plugin
     *
     * @param \Magento\Catalog\Model\Product $subject
     */
    public function beforeIsSaleable(\Magento\Catalog\Model\Product $subject)
    {
        if ($this->request->getFullActionName() == 'catalog_product_view') {
            $this->productHelper->setSkipSaleableCheck(true);
        }
    }
}
