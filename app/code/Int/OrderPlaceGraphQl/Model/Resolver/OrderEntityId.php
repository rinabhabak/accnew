<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_CustomerGraphQl
 * @author    Indusnet
 */

namespace Int\OrderPlaceGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class OrderEntityId
 * @package Int\CustomerGraphQl\Model\Resolver
 */
class OrderEntityId implements ResolverInterface
{

    protected $orderFactory;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    public function __construct(
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
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
    ) {        

        $incrementId = $value['order_number'];
        $orderId = '';
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if ($order->getId()) {
            $orderId = $order->getId();
            //$this->orderSender->send($order, true);
        }

       return $orderId;

    }

}