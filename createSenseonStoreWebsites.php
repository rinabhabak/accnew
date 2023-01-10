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
$website->load('senseon');

if(!$website->getId()){
    $website->setCode('senseon');
    $website->setName('Senseon Ecomm');
    $website->setDefaultGroupId(3);
    $websiteResourceModel->save($website);
	echo "<br /> Website Created: ".$website->getCode();
}

if($website->getId()){
    /** @var \Magento\Store\Model\Group $group */
    $group = $groupFactory->create();
    $group->setWebsiteId($website->getWebsiteId());
    $group->setCode('senseon_ecomm');
    $group->setName('Senseon Ecomm');
    $group->setRootCategoryId(88);
    $group->setDefaultStoreId(3);
    $groupResourceModel->save($group);
    
    echo "<br /> Store Created: ".$group->getCode();
}

/** @var  \Magento\Store\Model\Store $store */
$store = $storeFactory->create();
$store->load('senseon_ecomm');
if(!$store->getId()){
    $group = $groupFactory->create();
    $group->load('Senseon EComm', 'name');
    $store->setCode('senseon_ecomm');
    $store->setName('Senseon EComm Store View');
    $store->setWebsite($website);
    $store->setGroupId($group->getId());
    $store->setData('is_active','1');
    $storeResourceModel->save($store);
    
    echo "<br /> Store View Created: ".$store->getCode();
    // Trigger event to insert some data to the sales_sequence_meta table (fix bug place order in checkout)
    $this->eventManager->dispatch('store_add', ['store' => $store]);
}
