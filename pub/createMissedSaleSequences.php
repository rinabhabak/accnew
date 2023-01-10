<?php
require_once '../app/bootstrap.php';

$_SERVER[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'default';
$_SERVER[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'store';

/** @var \Magento\Framework\App\Bootstrap $bootstrap */
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);

/** @var \Magento\Framework\ObjectManager\ObjectManager $objectManager */
$objectManager = $bootstrap->getObjectManager();

/** @var \Magento\Framework\App\State $appState */
$appState = $objectManager->get('Magento\Framework\App\State');
$appState->setAreaCode(\Magento\Framework\App\Area::AREA_CRONTAB);

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\SalesSequence\Observer\SequenceCreatorObserver $sequenceCreator */
$sequenceCreator = $objectManager->get('Magento\SalesSequence\Observer\SequenceCreatorObserver');

/** @var Magento\Store\Model\StoreRepository $storeRepository */
$storeRepository = $objectManager->get('Magento\Store\Model\StoreRepository');
$storeList = $storeRepository->getList();

/** @var Magento\Framework\Event\Observer $observer */
$observer = $objectManager->get('Magento\Framework\Event\Observer');

foreach ($storeList as $store) {
    if ($store->getId() == 0) {
        continue;
    }
    $observer->setData('store', $store);
    $sequenceCreator->execute($observer);
}
