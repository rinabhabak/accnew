<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ProductsStoreWiseGraphQL\Model\Resolver\DataProvider;

class ProductsStore
{

    protected $_productCollectionFactory;
    protected $_imageHelper;
    protected $_appEmulation;
    protected $_storeManager;
    /**
     * @param  \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
         \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
         \Magento\Store\Model\App\Emulation $appEmulation,
         \Magento\Store\Model\StoreManagerInterface $storeManager,
         \Magento\Catalog\Helper\Image $imageHelper
    ) {
    	$this->_imageHelper = $imageHelper;
    	$this->_appEmulation = $appEmulation;
    	$this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    public function getProductsStore($store_id,$category_id,$products_per_page,$page_number,$sort_by,$sort_order,$filters)
    {
        $products = $this->_productCollectionFactory->create();
        $products->addAttributeToSelect('*');
        $products->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		$products->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		if($filters != "") {
			if (strpos($filters[0], ',') !== false) {
				$attr = explode(",",$filters[0]);
				foreach($attr as $a) {
					$ar[] = explode(":",$a);
				}
				
				foreach($ar as $val) {
					if (strpos($val[1], '|') !== false) {
					    $products->addAttributeToFilter(trim($val[0]), array('in' => explode("|",$val[1])));
					} else {
						$products->addAttributeToFilter(trim($val[0]), array('in' => $val[1]));
					}
				}
				
			} else {
				$ar[] = explode(":",$filters[0]);
				foreach($ar as $val) {
					if (strpos($val[1], '|') !== false) {
					    $products->addAttributeToFilter(trim($val[0]), array('in' => explode("|",$val[1])));
					} else {
						$products->addAttributeToFilter(trim($val[0]), array('in' => $val[1]));
					}
				}
			}
		}
        $products->addStoreFilter($store_id);
        $products->setOrder($sort_by, $sort_order);
        if($category_id > 0) {
			$products->addCategoriesFilter(['eq' => $category_id]);
		}
        $products->setPageSize($products_per_page);
        $products->setCurPage($page_number);
        
        $productsStore = [];
        
        $productsStore['totalProducts'] = $products->getSize();
        
        foreach($products as $k => $product) {
        	if($store_id == $product->getStoreId()) {
        		$regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getValue();
				$specialPrice = $product->getPriceInfo()->getPrice('special_price')->getValue();
				
				if ($product->getTypeId() == 'configurable') {
				      	$basePrice = $product->getPriceInfo()->getPrice('regular_price');
				      	$regularPrice = $basePrice->getMinRegularAmount()->getValue();
				      	$specialPrice = $product->getFinalPrice();
				}
				
				if ($product->getTypeId() == 'bundle') {
				      $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
				      $specialPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();            
				}
				
				if ($product->getTypeId() == 'grouped') {
				      $usedProds = $product->getTypeInstance(true)->getAssociatedProducts($product);            
				      foreach ($usedProds as $child) {
				          if ($child->getId() != $product->getId()) {
				                $regularPrice += $child->getPrice();
				                $specialPrice += $child->getFinalPrice();
				          }
				      }
				}
				
				$storeId = $this->_storeManager->getStore()->getId();
				$this->_appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
				$image_url = $this->_imageHelper->init($product, 'product_base_image')->getUrl();
				$this->_appEmulation->stopEnvironmentEmulation();
				
				
				$productsStore['products'][$k]['productId'] = $product->getId();
				$productsStore['products'][$k]['productSku'] = $product->getSku();
	            $productsStore['products'][$k]['productName'] = $product->getName();
	            $productsStore['products'][$k]['regularPrice'] = $regularPrice;
	            $productsStore['products'][$k]['specialPrice'] = $specialPrice;
	            $productsStore['products'][$k]['productImage'] = $image_url;
	            $productsStore['products'][$k]['productStatus'] = $product->getStatus();
	            $productsStore['products'][$k]['productVisibility'] = $product->getVisibility();
	            $productsStore['products'][$k]['storeId'] = $product->getStoreId();
			}
       	}
       	
		return $productsStore;
    }
}
