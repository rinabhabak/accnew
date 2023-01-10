<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\CartGraphQL\Model\Resolver\DataProvider;

class CartDetails
{
	protected $_cart;
	protected $_imageHelper;
	protected $_checkoutSession;
	
    public function __construct(
		\Magento\Checkout\Model\Cart $cart,
		\Magento\Catalog\Helper\Image $imageHelper,
		\Magento\Checkout\Model\Session $checkoutSession
	){
        $this->_cart = $cart;
        $this->_imageHelper = $imageHelper;
        $this->_checkoutSession = $checkoutSession;
    }

    public function getCartDetails()
    {
        $cartDetails = [];
        
        $items = $this->_cart->getQuote()->getAllVisibleItems();
        $totalItems = $this->_cart->getQuote()->getItemsCount();
		$totalQuantity = $this->_cart->getQuote()->getItemsQty();
		
		$address = $this->_cart->getQuote()->isVirtual() ? $this->_cart->getQuote()->getBillingAddress() : $this->_cart->getQuote()->getShippingAddress();
		$this->_checkoutSession->getQuote()->collectTotals()->save();
        $grandTotal = $address->getGrandTotal();
        $subTotal = $address->getSubtotal();
        
        $tax = $address->getTaxAmount();
        $shipping = $address->getShippingInclTax();
		
        foreach($items as $k => $item) {
        	$cartDetails['items'][$k]["product_thumbnail"] = $item->getProduct()->getThumbnail();
-		    $cartDetails['items'][$k]["product_id"] = $item->getProductId();
-		    $cartDetails['items'][$k]["product_name"] = $item->getName();
-		    $cartDetails['items'][$k]["product_sku"] = $item->getSku();
-		    $cartDetails['items'][$k]["product_qty"] = $item->getQty();
-		    $cartDetails['items'][$k]["product_price"] = $item->getPrice();
-		    $cartDetails['items'][$k]["product_subtotal"] = $item->getQty() * $item->getPrice();
-		    $cartDetails["total_items"] = $totalItems;
-		    $cartDetails["total_quantity"] = $totalQuantity;
		    $cartDetails["subtotal"] = $subTotal;
		    $cartDetails["shipping"] = $shipping;
		    $cartDetails["tax"] = $tax;
		    $cartDetails["grandtotal"] = $grandTotal;
		   
		    $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        	if(array_key_exists("options",$options)) {
				$customOptions = $options['options'];
				if (!empty($customOptions)) {
			        foreach ($customOptions as $j => $option) {
			            $cartDetails['items'][$k]["options"][$j]['option_label'] = $option['label'];
			            $cartDetails['items'][$k]["options"][$j]['option_value'] = $option['value'];
			        }
			    }
			} elseif(array_key_exists("attributes_info",$options)) {
				$configurableOptions = $options['attributes_info'];
			    foreach ($configurableOptions as $j => $option) {
			    	$cartDetails['items'][$k]["options"][$j]['option_label'] = $option['label'];
					$cartDetails['items'][$k]["options"][$j]['option_value'] = $option['value'];
			    }
			}
		}
		
		return $cartDetails;
    }
}

