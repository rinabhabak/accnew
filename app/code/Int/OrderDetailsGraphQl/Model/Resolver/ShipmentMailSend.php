<?php
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;

/**
 * Retrieves the ShipmentMailSend information object
 */
class ShipmentMailSend implements ResolverInterface
{    
    
 
   /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;
    protected $shipmentSender;
 

    public function __construct(
       ShipmentRepositoryInterface $shipmentRepository,
       \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender  
    )
    {
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentSender = $shipmentSender;
    }

    /**
     * Get All Product Items of Order.
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        
        $shipmentId = $args['shipmentId'];
        try {
        

       $shipment = $this->shipmentRepository->get($shipmentId);
        if (!$shipment->getEntityId()) {
            throw new GraphQlInputException(__('Shipment not found.'));
            return false;
        }
             $this->shipmentSender->send($shipment);
      
       return [
                "message" => __('Shipment Email has been sent successfully.')
            ];
       } catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }

    }
}
