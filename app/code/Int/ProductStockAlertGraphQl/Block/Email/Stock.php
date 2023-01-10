<?php

namespace Int\ProductStockAlertGraphQl\Block\Email;

class Stock extends \Bss\ProductStockAlert\Block\Email\Stock
{
    public function getProductUrl($product)
    {
        if (!$product) {
            return '';
        }

        $productUrl = $product->getUrlModel()->getUrl($product);
        $setUrl = explode($this->getStore()->getBaseUrl(), $productUrl);

        $finalRedirect = $this->getStore()->getBaseUrl().'products/';
        if(isset($setUrl[1])){
            $finalRedirect .=$setUrl[1];
        }
        return $finalRedirect;
    }
}