<?php
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

/**
 * Retrieves the AllInvoiceMailSend information object
 */
class AllInvoiceMailSend implements ResolverInterface
{    
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderModel;
 
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    public function __construct(
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository    
    )
    {
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;   
        $this->orderRepository = $orderRepository;
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

       foreach ($order->getInvoiceCollection() as $invoice) {
             $this->invoiceSender->send($invoice);
       }
       return [
                "message" => __('Invoice Email has been sent successfully.')
            ];
       } catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }

    }
}
