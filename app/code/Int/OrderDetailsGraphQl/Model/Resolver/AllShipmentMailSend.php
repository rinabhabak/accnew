<?php
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

/**
 * Retrieves the AllShipmentMailSend information object
 */
class AllShipmentMailSend implements ResolverInterface
{    
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    protected $shipmentSender;
   

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository ,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender   
    )
    {
        $this->orderRepository = $orderRepository;
        $this->shipmentSender = $shipmentSender;
    }

    /**
     * Get All Product Items of Order.
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        
        $orderId = $args['orderId'];
        try {
        $order = $this->orderRepository->get($orderId);
        if (!$order->getEntityId()) {

            throw new GraphQlInputException(__('Order not found.'));
            return false;
        }

       foreach ($order->getShipmentsCollection() as $shipment) {
             $this->shipmentSender->send($shipment);
       }
       return [
                "message" => __('Shipment Email has been sent successfully.')
            ];
       } catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }

    }
}
