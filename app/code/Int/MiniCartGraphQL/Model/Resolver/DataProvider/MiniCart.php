<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\MiniCartGraphQL\Model\Resolver\DataProvider;

class MiniCart
{
	protected $_cart;
	
    public function __construct(\Magento\Checkout\Model\Cart $cart)
    {
        $this->_cart = $cart;
    }

    public function getMiniCart()
    {
    	$cartDetails = [];
        $items = $this->_cart->getQuote()->getAllVisibleItems();
        $totalItems = $this->_cart->getQuote()->getItemsCount();
		$totalQuantity = $this->_cart->getQuote()->getItemsQty();
		$subTotal = $this->_cart->getQuote()->getSubtotal();
		$grandTotal = $this->_cart->getQuote()->getGrandTotal();
		
        foreach($items as $k => $item) {
        	$cartDetails['items'][$k]["productThumbnail"] = $item->getProduct()->getThumbnail();
		    $cartDetails['items'][$k]["productId"] = $item->getProductId();
		    $cartDetails['items'][$k]["productName"] = $item->getName();
		    $cartDetails['items'][$k]["productSku"] = $item->getSku();
		    $cartDetails['items'][$k]["productQty"] = $item->getQty();
		    $cartDetails['items'][$k]["productPrice"] = $item->getPrice();
		    $cartDetails["totalItems"] = $totalItems;
		    $cartDetails["totalQuantity"] = $totalQuantity;
		    $cartDetails["subtotal"] = $subTotal;
		    $cartDetails["grandtotal"] = $grandTotal;
		    
		    $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        	if(array_key_exists("options",$options)) {
				$customOptions = $options['options'];
				if (!empty($customOptions)) {
			        foreach ($customOptions as $j => $option) {
			            $cartDetails['items'][$k]["options"][$j]['optionLabel'] = $option['label'];
			            $cartDetails['items'][$k]["options"][$j]['optionValue'] = $option['value'];
			        }
			    }
			} elseif(array_key_exists("attributes_info",$options)) {
				$configurableOptions = $options['attributes_info'];
			    foreach ($configurableOptions as $j => $option) {
			    	$cartDetails['items'][$k]["options"][$j]['optionLabel'] = $option['label'];
			            $cartDetails['items'][$k]["options"][$j]['optionValue'] = $option['value'];
			    }
			}
		}
		
		return $cartDetails;
    }
}
