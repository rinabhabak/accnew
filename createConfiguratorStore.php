<?php
use \Magento\Framework\App\Bootstrap;

include('./app/bootstrap.php');
$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$websiteFactory = $objectManager->get('\Magento\Store\Model\WebsiteFactory');
$websiteResourceModel = $objectManager->get('\Magento\Store\Model\ResourceModel\Website');
$groupFactory = $objectManager->get('\Magento\Store\Model\GroupFactory');
$groupResourceModel = $objectManager->get('\Magento\Store\Model\ResourceModel\Group');
$storeFactory = $objectManager->get('\Magento\Store\Model\StoreFactory');
$storeResourceModel = $objectManager->get('\Magento\Store\Model\ResourceModel\Store');

$website = $websiteFactory->create();
$website->load('base');

if($website->getId()){
    /** @var \Magento\Store\Model\Group $group */
    $group = $groupFactory->create();
    $group->setWebsiteId($website->getWebsiteId());
    $group->setCode('configurator');
    $group->setName('Configurator');
    $group->setRootCategoryId(2);
    $group->setDefaultStoreId(3);
    $groupResourceModel->save($group);
    
    echo "<br /> Store Created: ".$group->getCode();
}

/** @var  \Magento\Store\Model\Store $store */
$store = $storeFactory->create();
$store->load('configurator');
if(!$store->getId()){
    $group = $groupFactory->create();
    $group->load('Configurator', 'name');
    $store->setCode('configurator');
    $store->setName('Configurator Store View');
    $store->setWebsite($website);
    $store->setGroupId($group->getId());
    $store->setData('is_active','1');
    $storeResourceModel->save($store);
    
    echo "<br /> Store View Created: ".$store->getCode();
    // Trigger event to insert some data to the sales_sequence_meta table (fix bug place order in checkout)
    $this->eventManager->dispatch('store_add', ['store' => $store]);
}