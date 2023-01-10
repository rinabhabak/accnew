<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Products\StoreWiseGraphQL\Model\Resolver\DataProvider;

class ProductsStore
{

    protected $_productCollectionFactory;

    /**
     * @param  \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
         \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    public function getProductsStore($store_id)
    {
        $products = $this->_productCollectionFactory->create();
        $products->addAttributeToSelect('*');
        $products->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		$products->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $products->addStoreFilter($store_id);
        
        $productsStore = [];
        
        foreach($products as $k => $product) {
        	if($store_id == $product->getStoreId()) {
				$productsStore['products'][$k]['product_id'] = $product->getId();
	            $productsStore['products'][$k]['product_name'] = $product->getName();
	            $productsStore['products'][$k]['product_status'] = $product->getStatus();
	            $productsStore['products'][$k]['product_visibility'] = $product->getVisibility();
	            $productsStore['products'][$k]['store_id'] = $product->getStoreId();
			}
       	}
       	
		return $productsStore;
    }
}

