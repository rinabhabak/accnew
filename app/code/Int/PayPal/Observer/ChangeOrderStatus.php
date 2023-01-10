<?php namespace Int\PayPal\Observer;
    use Magento\Framework\Event\ObserverInterface;
    use Magento\Sales\Model\Order;

    class ChangeOrderStatus implements ObserverInterface {

        protected $_order;

        public function __construct(
            \Magento\Sales\Model\Order $_order
        ) {
            $this->_order = $_order;
        }

        public function execute(\Magento\Framework\Event\Observer $observer) {
            $order = $observer->getEvent()->getOrder();
            $orderId = $order->getId();
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/orderstatus.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($orderId);
            $orderState = Order::STATE_PENDING_PAYMENT;
            $_orderLoaded = $this->_order->load($orderId);
            $_orderLoaded->setState($orderState)->setStatus(Order::STATE_PENDING_PAYMENT);
       }
   }