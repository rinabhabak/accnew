<?php
/**
 * Copyright © Indusnet, Inc. All rights reserved.
 */
declare(strict_types=1);

namespace Int\CoreGraphQl\Plugin;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class RelatedProductDataProviderPlugin
{
    protected $productCollectionFactory;

    public function __construct(      
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ){
        $this->productCollectionFactory = $productCollectionFactory;
    }

    protected function getVisibleProductCollection($productId, $productIds)
    {
        if(empty($productIds) || empty($productId)){
            return [];
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addIdFilter($productIds);
        $collection->addAttributeToFilter('visibility', Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $map[$productId] = [];
        if($collection->count()){
            foreach($collection as $item){
                $map[$productId][] = $item->getId();
            }
        }
        return $map;
    }

	public function afterGetRelations(
        \Magento\RelatedProductGraphQl\Model\DataProvider\RelatedProductDataProvider $subject, 
        $result
    ){
        if(empty($result)){
            return [];
        }
        
        $productIds = [];
        foreach($result as $mainProductId => $relatedProductId){
            $productId = $mainProductId;
            $productIds[] = $relatedProductId;
        }
    
		return $this->getVisibleProductCollection($productId, $productIds);
	}

}