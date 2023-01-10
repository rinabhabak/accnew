<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Stores\GetListing\Model\Resolver\DataProvider;

class AllStores
{
    protected $storeList;
    /**
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeList
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeList
    ) {
        $this->storeList = $storeList;
    }

    public function getAllStores()
    {
        $stores = $this->storeList->getList();
        
        $storeAll = [];
        
        foreach($stores as $k => $store) {
        	$storeAll['stores'][$k]['store_id'] = $store->getId();
            $storeAll['stores'][$k]['code'] = $store->getCode();
            $storeAll['stores'][$k]['store_name'] = $store->getName();
            $storeAll['stores'][$k]['website_id'] = $store->getWebsiteId();
            $storeAll['stores'][$k]['store_active'] = $store->isActive();
       	}
       	
		return $storeAll;
    }
    
    public function getStoreById($store_id)
    {
        $stores = $this->storeList->getList();
        
        $storeAll = [];
        
        foreach($stores as $k => $store) {
        	if($store_id == $store->getId()) {
				$storeAll['stores'][$k]['store_id'] = $store->getId();
	            $storeAll['stores'][$k]['code'] = $store->getCode();
	            $storeAll['stores'][$k]['store_name'] = $store->getName();
	            $storeAll['stores'][$k]['website_id'] = $store->getWebsiteId();
	            $storeAll['stores'][$k]['store_active'] = $store->isActive();	
			}
       	}
       	
		return $storeAll;
    }
}

