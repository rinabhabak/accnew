<?php
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

/**
 * Retrieves the InvoiceMailSend information object
 */
class InvoiceMailSend implements ResolverInterface
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
        \Magento\Sales\Model\Order\Invoice $invoice
    )
    {
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;   
        $this->invoice = $invoice;    
    }

    /**
     * Get All Product Items of Order.
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        
        $invoiceId = $args['invoiceId'];
        try {
        $invoice = $this->invoice->load($invoiceId);

        if (!$invoice->getEntityId()) {
            throw new GraphQlInputException(__('Invoice not found.'));
            return false;
        }

       
             $this->invoiceSender->send($invoice);
     
       return [
                "message" => __('Invoice Email has been sent successfully.')
            ];
       } catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }

    }
}
