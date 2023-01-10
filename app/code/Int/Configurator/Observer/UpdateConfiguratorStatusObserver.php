<?php
    namespace Int\Configurator\Observer;

    use Magento\Framework\Event\ObserverInterface;

    class UpdateConfiguratorStatusObserver implements ObserverInterface
    {
        protected $_statusFactory;

        protected $timezone;

        public function __construct(
            \Int\CustomerHistoryUpdates\Model\StatusFactory $statusFactory,
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
        ) {
            $this->_statusFactory = $statusFactory;
            $this->timezone = $timezone;
        }

        public function execute(\Magento\Framework\Event\Observer $observer)
        {
            $order = $observer->getEvent()->getOrder();

            $incrementId = $order->getIncrementId();

            $customerId = $order->getCustomerId();

            $configuratorPids = $order->getConfiguratorPid();

            $configuratorPidInfo = explode(',', $configuratorPids);

            if(!empty($configuratorPids))
            {
                foreach($configuratorPidInfo as $value)
                {
                    $configuratorPid = trim($value);
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $configuratorModel = $objectManager->Create('Int\Configurator\Model\Configurator')->load($configuratorPid, 'project_id');
                    $configuratorModel->setStatus(\Int\Configurator\Model\Status::STATUS_PURCHASED);
                    $configuratorModel->setBdsStatus(\Int\Configurator\Model\Status::STATUS_PURCHASED);
                    $configuratorModel->save();

                    $customerHistoryStatus = $this->_statusFactory->create();
                    $customerHistoryStatus->setCustomerId($customerId);
                    $customerHistoryStatus->setStatus('1');
                    $customerHistoryStatus->setConfiguratorId($configuratorModel->getId());
                    $customerHistoryStatus->setMessage(__('We have received the order #'.$incrementId.' for the configurator '.$configuratorPid));
                    $customerHistoryStatus->setCreatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
                    $customerHistoryStatus->setUpdatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
                    $customerHistoryStatus->save();
                }
            }  
        }
    }