<?php
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\Websites\Collection;
/**
 * Retrieves the Shipment information object
 */
class Shipment implements ResolverInterface
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
       
      
       $shipmentData = [];
       
      if ($order->hasShipments()) {
        $a=0;
        $b=0;
            foreach ($order->getShipmentsCollection() as $shipment) {
               $shipmentData[$a]['shipment_id'] = $shipment->getEntityId();
                $shipmentData[$a]['shipment_increment_id'] = $shipment->getIncrementId();

                 $getAllProducts = $shipment->getAllItems();
                 
              foreach ($getAllProducts as $item) {

                    $itemCollection = $this->orderItemRepository->get($item->getorder_item_id());
                    $k=0;
                    if($itemCollection->getProductType() == 'configurable')
                        {
                            $configurable_attributes = $itemCollection->getproduct_options()['attributes_info'];
               
                            foreach($configurable_attributes as $attributes_info){
                                $shipmentData[$a]['shipmentItems'][$b]['productoptions'][$k]['label'] = $attributes_info['label'];
                                $shipmentData[$a]['shipmentItems'][$b]['productoptions'][$k]['value'] = $attributes_info['value'];
                                $k++;
                            }
                
                
                        }


                    $shipmentData[$a]['shipmentItems'][$b]['title'] = $item->getName();
                    $shipmentData[$a]['shipmentItems'][$b]['sku'] = $item->getSku();
                    $shipmentData[$a]['shipmentItems'][$b]['qty'] = $item->getQty();
                    $shipmentData[$a]['shipmentItems'][$b]['product_type'] = $itemCollection->getProductType();
                    $b++;
                }
                $a++;
            }
        }
       
        return  $shipmentData;
    }
}
