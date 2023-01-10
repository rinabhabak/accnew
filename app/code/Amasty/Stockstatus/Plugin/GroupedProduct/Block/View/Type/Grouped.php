<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
namespace Amasty\Stockstatus\Plugin\GroupedProduct\Block\View\Type;

class Grouped
{
    /**
     * @var \Amasty\Stockstatus\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Amasty\Stockstatus\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\GroupedProduct\Block\Product\View\Type\Grouped $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return mixed|string
     */
    public function aroundGetProductPrice(
        \Magento\GroupedProduct\Block\Product\View\Type\Grouped $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $result = $proceed($product);

        $status = $this->helper->getCartStockStatus($product->getData('sku'));
        if ($status) {
            $status = '<p>' . $status . '</p>' ;
            $result = $status . $result;
        }

        return $result;
    }
}
