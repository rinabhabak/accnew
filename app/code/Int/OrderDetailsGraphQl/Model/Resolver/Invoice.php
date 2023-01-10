<?php
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\Websites\Collection;
/**
 * Retrieves the Invoice information object
 */
class Invoice implements ResolverInterface
{    

    protected $invoiceFactory;
    protected $timezone;


    public function __construct(
        
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository

    ) {
         $this->orderRepository = $orderRepository;
         $this->timezone = $timezone;
         $this->orderItemRepository = $orderItemRepository;
    }
   
    /**
     * Get All Product Items of Order.
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        
        if (!isset($value['order_id'])) {
             return null;
        }
       $order = $this->orderRepository->get($value['order_id']);       
       $invoice_id = [];
       $invoiceData = [];
       if ($order->hasInvoices()) {
        $i=0;
        $j=0;
            foreach ($order->getInvoiceCollection() as $invoice) {
                $invoiceData[$i]['invoice_id'] = $invoice->getEntityId();
                $invoiceData[$i]['invoice_increment_id'] = $invoice->getIncrementId();
                $invoiceData[$i]['sub_total'] = $invoice->getSubtotal();
                $invoiceData[$i]['base_sub_total'] = $invoice->getBaseSubtotal();
                $invoiceData[$i]['shipping_amount'] = $invoice->getShippingAmount();
                $invoiceData[$i]['base_shipping_amount'] = $invoice->getBaseShippingAmount();
                $invoiceData[$i]['grand_total'] = $invoice->getGrandTotal();
                $invoiceData[$i]['base_grand_total'] = $invoice->getBaseGrandTotal();

                $created_at = $this->timezone->date($invoice->getCreatedAt())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                $invoiceData[$i]['created_at'] = date('M d, Y',strtotime($created_at));
                 $getAllProducts = $invoice->getAllItems();
                foreach ($getAllProducts as $item) {

                    if ($item->getOrderItem()->getParentItem()) {
                        continue;
                    }

                    $itemCollection = $this->orderItemRepository->get($item->getorder_item_id());
                    $k=0;
                    if($itemCollection->getProductType() == 'configurable')
                        {
                            $configurable_attributes = $itemCollection->getproduct_options()['attributes_info'];
               
                            foreach($configurable_attributes as $attributes_info){
                                $invoiceData[$i]['invoiceItems'][$j]['productoptions'][$k]['label'] = $attributes_info['label'];
                                $invoiceData[$i]['invoiceItems'][$j]['productoptions'][$k]['value'] = $attributes_info['value'];
                                $k++;
                            }
                
                
                        }

                    $invoiceData[$i]['invoiceItems'][$j]['title'] = $item->getName();
                    $invoiceData[$i]['invoiceItems'][$j]['sku'] = $item->getSku();
                    $invoiceData[$i]['invoiceItems'][$j]['price'] = $item->getPrice();
                    $invoiceData[$i]['invoiceItems'][$j]['base_price'] = $item->getBasePrice();
                    $invoiceData[$i]['invoiceItems'][$j]['qty'] = $item->getQty();
                    $invoiceData[$i]['invoiceItems'][$j]['product_type'] = $itemCollection->getProductType();
                    $invoiceData[$i]['invoiceItems'][$j]['sub_total'] = $item->getRowTotalInclTax();
                    $invoiceData[$i]['invoiceItems'][$j]['base_sub_total'] = $item->getBaseRowTotalInclTax();
                    $j++;
                }
                $i++;
            }
        }

        return $invoiceData;
    }
}
