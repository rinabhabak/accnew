<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\MyOrderDetailsGraphQL\Model\Resolver\DataProvider;

class MyOrderDetails
{
	protected $_order;
	
		
    public function __construct(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->_order = $order;
    }

    public function getMyOrderDetails($order_id)
    {
        $current_order = [];
    	
        if($order_id > 0) {
			$order = $this->_order->load($order_id);
            $payment_method = $order->getPayment()->getMethodInstance()->getTitle();
            
            $billing_address_street = $order->getBillingAddress()->getStreet();
            $shipping_address_street = $order->getShippingAddress()->getStreet();
            
            foreach ($order->getAllVisibleItems() as $k => $item) {
			    $current_order['orderItems'][$k]["productId"] = $item->getProductId();
			    $current_order['orderItems'][$k]["productName"] = $item->getName();
			    $current_order['orderItems'][$k]["productSku"] = $item->getSku();
			    $current_order['orderItems'][$k]["productQty"] = $item->getQtyOrdered();
			    $current_order['orderItems'][$k]["productPrice"] = $item->getPrice();	
			    
				if($_options = $this->getSelectedOptions($item)) {
			            foreach ($_options as $j => $_option) {
			            	$current_order['orderItems'][$k]["options"][$j]['optionLabel'] = $_option['label'];
			            	$current_order['orderItems'][$k]["options"][$j]['optionValue'] = $_option['value'];
			            }
			    }
			    
			}
			
			$current_order["orderId"] = $order["entity_id"];
			$current_order["incrementId"] =  $order["increment_id"];
			$current_order["status"] =  $order["status"];
			$current_order["storeId"] =  $order["store_id"];
			$current_order["customerId"] =  $order["customer_id"];
			$current_order["customerPrefix"] =  $order["customer_prefix"];
			$current_order["customerFirstname"] =  $order["customer_firstname"];
			$current_order["customerMiddlename"] =  $order["customer_middlename"];
			$current_order["customerLastname"] =  $order["customer_lastname"];
			$current_order["customerSuffix"] =  $order["customer_suffix"];
			$current_order["customerEmail"] =  $order["customer_email"];
			$current_order["customerPhoneNumber"] =  $order["customer_phone_number"];
			$current_order["shippingMethod"] =  $order["shipping_method"];
			$current_order["shippingDescription"] =  $order["shipping_description"];
			$current_order["paymentMethod"] =  $payment_method;
			$current_order["subtotal"] =  $order["subtotal"];
			$current_order["shippingAmount"] =  $order["shipping_amount"];
			$current_order["taxAmount"] =  $order["tax_amount"];
			$current_order["discountAmount"] =  $order["discount_amount"];
			$current_order["grandTotal"] =  $order["grand_total"];
			$current_order["customerIsGuest"] =  $order["customer_is_guest"];
			$current_order["billingName"] = $order->getBillingAddress()->getFirstname()." ".$order->getBillingAddress()->getLastname();
			$current_order["billingStreet"] =  $billing_address_street[0];
			$current_order["billingCity"] =  $order->getBillingAddress()->getCity();
			$current_order["billingRegion"] =  $order->getBillingAddress()->getRegion();
			$current_order["billingPostcode"] =  $order->getBillingAddress()->getPostcode();
			$current_order["billingCountry"] =  $order->getBillingAddress()->getCountryId();
			$current_order["billingPhoneNumber"] =  $order->getBillingAddress()->getPhoneNumber();
			$current_order["shippingName"] =  $order->getShippingAddress()->getFirstname()." ".$order->getShippingAddress()->getLastname();
			$current_order["shippingStreet"] =  $shipping_address_street[0];
			$current_order["shippingCity"] =  $order->getShippingAddress()->getCity();
			$current_order["shippingRegion"] =  $order->getShippingAddress()->getRegion();
			$current_order["shippingPostcode"] =  $order->getShippingAddress()->getPostcode();
			$current_order["shippingCountry"] =  $order->getShippingAddress()->getCountryId();
			$current_order["shippingPhoneNumber"] =  $order->getShippingAddress()->getPhoneNumber();
			$current_order["isInvoiced"] =  $order->hasInvoices();
			$current_order["isShipped"] =  $order->hasShipments();
			$current_order["createdAt"] =  $order["created_at"];
			$current_order["updatedAt"] =  $order["updated_at"];
		}
		
		return $current_order;
    }
    
    public function getSelectedOptions($item){
     $result = [];
        $options = $item->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        return $result;
    }
}

