<?php
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

/**
 * Retrieves the OrderMailSend information object
 */
class OrderMailSend implements ResolverInterface
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
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    )
    {
        $this->orderModel = $orderModel;
        $this->orderSender = $orderSender;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        
        $orderId = $args['orderId'];
        try {
        $order = $this->orderModel->create()->load($orderId);
        if (!$order->getId()) {
            throw new GraphQlInputException(__('Order not found.'));
            return false;
        }

        $this->orderSender->send($order, true);
       return [
                "message" => __('Order Email has been sent successfully.')
            ];
       } catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }

    }
}
