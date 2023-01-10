<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\SalesGuestFormGraphQL\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\Order;

class GetGuestOrderStatus implements ResolverInterface
{
    /**
    * @var \Magento\Sales\Api\OrderRepositoryInterface
    */
    protected $orderRepository;

    /**
    * @var \Magento\Framework\Api\SearchCriteriaBuilder
    */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Cybersource\Model\Source\CcType
     */
    protected $_ccType;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria,
        \Magedelight\Cybersource\Model\Source\CcType $ccType,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ){
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteria;
        $this->storeManager = $storeManager;
        $this->_productFactory = $productFactory;
        $this->_ccType = $ccType;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ){
        if(empty($args['orderId'])) {
            throw new GraphQlInputException(__('"orderId" value should be specified'));
        }

        if(empty($args['billing_lastname'])) {
            throw new GraphQlInputException(__('"billing_lastname" value should be specified'));
        }

        if(empty($args['billing_user_email']) && empty($args['billing_zip_code'])) {
            throw new GraphQlInputException(__('"billing_user_email or billing_zip_code" value should be specified'));
        }

        $order = $this->loadFromPost($args);

        return $this->formatedReturn($order);
    }

    /**
     * Load order data from post
     *
     * @param array $postData
     * @return Order
     * @throws GraphQlInputException
     */
    protected function loadFromPost(array $postData)
    {
        $order = $this->getOrderRecord($postData['orderId']);

        if (!$this->compareStoredBillingDataWithInput($order, $postData)) {
            throw new GraphQlInputException(__('You have entered incorrect data. Please try again.'));
        }

        return $order;
    }

    /**
     * Check that billing data from the order and from the input are equal
     *
     * @param Order $order
     * @param array $postData
     * @return bool
     */
    protected function compareStoredBillingDataWithInput(Order $order, array $postData)
    {
        $email = $postData['billing_user_email'];
        $lastName = $postData['billing_lastname'];
        $zip = $postData['billing_zip_code'];
        $billingAddress = $order->getBillingAddress();
        return strtolower($lastName) === strtolower($billingAddress->getLastname()) &&
            (!empty($email) && strtolower($email) === strtolower($billingAddress->getEmail()) || 
            	!empty($zip) && strtolower($zip) === strtolower($billingAddress->getPostcode()));
    }

    /**
     * Get order by increment_id and store_id
     *
     * @param string $incrementId
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws GraphQlInputException
     */
    protected function getOrderRecord($incrementId)
    {
        $records = $this->orderRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter('increment_id', $incrementId)
                ->addFilter('store_id', $this->storeManager->getStore()->getId())
                ->create()
        );

        $items = $records->getItems();
        
        if(empty($items)) {
            throw new GraphQlInputException(__('You have entered incorrect data. Please try again.'));
        }

        return array_shift($items);
    }

    /**
     * Return Formatted Response to GraphQl
     *
     * @param Order $order
     * @return array
     */
    protected function formatedReturn(Order $order)
    {
        try{
            return [
                'order_id' => $order->getId(),
                'order_increment_id' => $order->getIncrementId(),
                'state' => $order->getState(),
                'shipping_method' => $order->getShippingDescription(),
                'grand_total' => $order->getGrandTotal(),
                'shipping_amount' => $order->getShippingAmount(),
                'subtotal' => $order->getSubtotal(),
                'tax_amount' => !is_null($order->getTaxAmount())? $order->getTaxAmount():'0.0000',
                'discount_amount' => !is_null($order->getDiscountAmount())? $order->getDiscountAmount():'0.0000',
                'ordered_items' => $this->orderedItems($order),
                'purchase_date' => date('M d, Y',strtotime($order->getCreatedAt())),
                'shipping_address' => [
                    'name' => $order->getShippingAddress()->getName(),
                    'company' => $order->getShippingAddress()->getCompany(),
                    'street' => $order->getShippingAddress()->getStreet(),
                    'city' => $order->getShippingAddress()->getCity(),
                    'region' => $order->getShippingAddress()->getRegion(),
                    'country' => $order->getShippingAddress()->getCountryId(),
                    'zip_code' => $order->getShippingAddress()->getPostcode(),
                    'telephone' => $order->getShippingAddress()->getTelephone()
                ],
                'billing_address' => [
                    'name' => $order->getBillingAddress()->getName(),
                    'company' => $order->getBillingAddress()->getCompany(),
                    'street' => $order->getBillingAddress()->getStreet(),
                    'city' => $order->getBillingAddress()->getCity(),
                    'region' => $order->getBillingAddress()->getRegion(),
                    'country' => $order->getBillingAddress()->getCountryId(),
                    'zip_code' => $order->getBillingAddress()->getPostcode(),
                    'telephone' => $order->getBillingAddress()->getTelephone()
                ],
                'payment_information' => [
                    'method_name' => $order->getPayment()->getMethodInstance()->getTitle(),
                    'card_type' => $order->getPayment()->getCcType(),
                    'card_label' => $this->getCcTypeLabel($order),
                    'card_last4' => 'xxxx-'.$order->getPayment()->getCcLast4(),
                    'processed_amount' => $order->getPayment()->getAmountAuthorized()
                ],
                'order_invoice' => $this->orderInvoiceData($order),
                'order_shipment' => $this->orderShipmentData($order)
            ];
        } catch (\Exception $e) {
            throw new GraphQlInputException($e->getMessage());
        }
    }

    /**
     * Return Credit/Debit Card Label to GraphQl
     *
     * @param Order $order
     * @return string
     */
    protected function getCcTypeLabel(Order $order)
    {
        $allOptions = $this->_ccType->toOptionArray();
        $cctype = $order->getPayment()->getCcType();
        foreach ($allOptions as $option) {
            if($option['value'] === $cctype){
                return $option['label'];
            }
        }
    }

    /**
     * Return Ordered Items to GraphQl
     *
     * @param Order $order
     * @return array
     */
    protected function orderedItems(Order $order)
    {
    	$orderItems = [];

    	foreach ($order->getAllItems() as $item) {

            $parent_sku = null;

            if($item->getProductType() === 'configurable'){
				$_product = $this->_productFactory->create()->load($item->getProductId());
				$parent_sku = $_product->getSku();
            }
   
            if($item->getRowTotal() != '0'){
                $orderItems[] = [
                    'name' => $item->getName(),
                    'productoptions' => $this->productOptions($item),
                    'sku' => $item->getSku(),
                    'parent_sku' => $parent_sku,
                    'price' => $item->getPrice(),
                    'qty' => $item->getQtyOrdered(),
                    'product_type' => $item->getProductType(),
                    'subtotal' => $item->getRowTotal()
                ];
            }
    		
    	}

    	return $orderItems;
    }

    /**
     * Return Ordered Items to GraphQl
     *
     * @param object $item
     * @return array
     */
    protected function productOptions($item)
    {
    	$productOptions = [];

    	if($item->getProductType() === 'configurable')
    	{
    		$options = $item->getProductOptions();
    		foreach($options['attributes_info'] as $option) {
	    		$productOptions[] = [
	    			'label' => $option['label'],
	    			'value' => $option['value']
	    		];
	    	}
    	}
    	
    	return $productOptions;
    }

    /**
     * Return Invoice Items to GraphQl
     *
     * @param Order $order
     * @return array
     */
    protected function orderInvoiceData(Order $order)
    {
        $invoiceData = [];

        if ($order->hasInvoices()){
            foreach ($order->getInvoiceCollection() as $invoice){
                $invoiceData[] = [
                    'invoice_id' => $invoice->getEntityId(),
                    'invoice_increment_id' => $invoice->getIncrementId(),
                    'subtotal' => $invoice->getSubtotal(),
                    'shipping_amount' => $invoice->getShippingAmount(),
                    'grand_total' => $invoice->getGrandTotal(),
                    'created_at' => date('M d, Y',strtotime($invoice->getCreatedAt())),
                    'invoice_items' => $this->orderedItems($order)
                ];
            }
        }

    	return $invoiceData;
    }

    /**
     * Return Shipment Items to GraphQl
     *
     * @param Order $order
     * @return array
     */
    protected function orderShipmentData(Order $order)
    {
        $shipmentData = [];

        if ($order->hasShipments()){
            
            foreach ($order->getShipmentsCollection() as $shipment) {
                $shipmentData[] = [
                    'shipment_id' => $shipment->getEntityId(),
                    'shipment_increment_id' => $shipment->getIncrementId(),
                    'created_at' => date('M d, Y',strtotime($shipment->getCreatedAt())),
                    'shipment_items' => $this->orderedItems($order)
                ];
            }
        }

        return $shipmentData;
    }

}

