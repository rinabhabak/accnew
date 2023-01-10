<?php
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Sales Order field resolver, used for GraphQL request processing
 */
class SalesOrder implements ResolverInterface
{
    protected $timezone;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->orderRepository = $orderRepository;
        $this->timezone = $timezone;
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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $salesId = $this->getSalesId($args);
        $salesData = $this->getSalesData($salesId);

        return $salesData;
    }

    /**
     * @param array $args
     * @return int
     * @throws GraphQlInputException
     */
    private function getSalesId(array $args): int
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('"sales id should be specified'));
        }

        return (int)$args['id'];
    }

    /**
     * @param int $orderId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getSalesData(int $orderId): array
    {
        try {
            $order = $this->orderRepository->get($orderId);
            $billigAddress = $order->getBillingAddress()->getData();
            $shippingAddress = $order->getShippingAddress()->getData();
            $shippingMethod = $order->getShippingDescription();
            $payment_method = $order->getPayment()->getMethodInstance()->getTitle();
            $order_status = $order->getStatusLabel();
            $created_at = $this->timezone->date($order->getCreatedAt())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            $additionalInformation = $order->getPayment()->getAdditionalInformation();
            foreach ($order->getAllVisibleItems() as $_item) {
                $itemsData[] = $_item->getData();
            }

            $invoiceDetails = $order->getInvoiceCollection();
            $invoice_ids = [];
            foreach ($invoiceDetails as $invoice) {
               $invoice_ids[] = $invoice->getEntityId();
            }
            $pageData = [
                'increment_id' => $order->getIncrementId(),
                'configurator_pid' => $order->getConfiguratorPid(),
                'sub_total' => $order->getSubTotal(),
                'base_sub_total' => $order->getBaseSubtotal(),
                'shipping_amount' => $order->getShippingAmount(),
                'base_shipping_amount' => $order->getBaseShippingAmount(),
                'tax_amount' => $order->getTaxAmount(),
                'base_tax_amount' => $order->getBaseTaxAmount(),
                'grand_total' => $order->getGrandTotal(),
                'base_grand_total' => $order->getBaseGrandTotal(),
                'is_shipped' => $order->hasShipments(),
                'is_invoiced' => $order->hasInvoices(),
                'customer_name' => $order->getCustomerFirstname().' '.$order->getCustomerLastname(),
                'created_at' => date('F j, Y',strtotime($created_at)),
                'is_guest_customer' => !empty($order->getCustomerIsGuest()) ? 1 : 0,
                'shipping_method' => !empty($order->getShippingMethod()) ? $shippingMethod : null,
                'payment_method' => !empty($payment_method) ? $payment_method : null,
                'order_status' => !empty($order_status) ? $order_status : null,
                'shipping_address' => $shippingAddress,
                'billing_address' => $billigAddress,
                'items' => $itemsData,
                'card_details' => $additionalInformation,
                'order_id' => $orderId
            ];
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $pageData;
    }
}