<?php
namespace Int\Configurator\Observer;
 
use Magento\Framework\Event\ObserverInterface;
 
class ConfiguratorProjectIdObserver implements ObserverInterface
{
    protected $objectCopyService;
    public function __construct(
      \Magento\Framework\DataObject\Copy $objectCopyService
    ) {
        $this->objectCopyService = $objectCopyService;
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
      $order = $observer->getEvent()->getData('order');
      $quote = $observer->getEvent()->getData('quote');
      $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);
      return $this;
    }
}