<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ProductDetailGraphQL\Model\Resolver\DataProvider;

class ProductDetail
{

    protected $_registry;
    protected $_productloader;
    protected $_repository;
    protected $_reviewCollection;
    protected $_reviewFactory;
    protected $_storeManager;
    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(\Magento\Framework\Registry $registry,
    \Magento\Catalog\Model\ProductFactory $_productloader,
    \Magento\Catalog\Model\ProductRepository $repository,
    \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollection,
    \Magento\Review\Model\ReviewFactory $reviewFactory,
    \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_registry = $registry;
        $this->_productloader = $_productloader;
        $this->_repository = $repository;
        $this->_reviewCollection = $reviewCollection;
        $this->_reviewFactory = $reviewFactory;
    	$this->_storeManager = $storeManager;
    }

    public function getProductDetail($product_id)
    {
    	$current_product = [];
    	
        if($product_id > 0) {
			$product = $this->_productloader->create()->load($product_id);
		} else {
			$product = $this->_registry->registry('current_product');
		}
		
		if($product) {
			$regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getValue();
			$specialPrice = $product->getPriceInfo()->getPrice('special_price')->getValue();
			
			if ($product->getTypeId() == 'configurable') {
			      	$basePrice = $product->getPriceInfo()->getPrice('regular_price');
			      	$regularPrice = $basePrice->getMinRegularAmount()->getValue();
			      	$specialPrice = $product->getFinalPrice();
			      	
			      	$data = $product->getTypeInstance()->getConfigurableOptions($product);
					$options = array();
					
					foreach($data as $attr){
					  foreach($attr as $p){
					    $options[$p['sku']][$p['attribute_code']] = $p['option_title'];
					  }
					}
					
					foreach($options as $sku =>$d){
					  $pr = $this->_repository->get($sku);
					  $current_product['configurables'][$sku]["configurationSku"] = $sku;
					  foreach($d as $k => $v) {
					  	$current_product['configurables'][$sku]["configurations"][$k]["configurationLabel"] = $k;
					  	$current_product['configurables'][$sku]["configurations"][$k]["configurationValue"] = $v;
					  }
					  $current_product['configurables'][$sku]["configurationPrice"] = $pr->getPrice();
					  
					  $childOptionId = $pr->getUom();   
					  $attr = $pr->getResource()->getAttribute('uom');
					  if ($attr->usesSource()) {
						$childOptionText = $attr->getSource()->getOptionText($childOptionId);
					  }
					  $current_product['configurables'][$sku]["configurationUnitOfMeasurement"] = $childOptionText;
					}
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
			
			$optionId = $product->getUom();   
			$attr = $product->getResource()->getAttribute('uom');
			if ($attr->usesSource()) {
			    $optionText = $attr->getSource()->getOptionText($optionId);
			}
			
			$current_product['productId'] = $product->getId();
		    $current_product['productName'] = $product->getName();
		    $current_product['productSku'] = $product->getSku();
		    $current_product['regularPrice'] = $regularPrice;
		    $current_product['specialPrice'] = $specialPrice;
		    $current_product['unitOfMeasurement'] = $optionText;
		    $current_product['description'] = $product->getDescription();
		    $current_product['techSpecification'] = $product->getTechSpecification();
		    $current_product['resources'] = $product->getResources();
		    $current_product['systemCompatibility'] = $product->getSystemCompatibility();
		    
		    $images = $product->getMediaGalleryImages();
			foreach($images as $k => $image){
			    $current_product['productImages'][$k]["image"] = $image->getUrl();
			}
			
			$relatedProducts = $product->getRelatedProducts();

			if (!empty($relatedProducts)) {
			    foreach ($relatedProducts as $k => $relatedProduct) {
			        $_product = $this->_productloader->create()->load($relatedProduct->getId());
			        $current_product['relatedProducts'][$k]["productId"] = $relatedProduct->getId();
			        $current_product['relatedProducts'][$k]["productName"] = $_product->getName();
			        
			        $relatedImages = $_product->getMediaGalleryImages();
					foreach($relatedImages as $j => $relatedImage){
					    $current_product['relatedProducts'][$k]["productImages"][$j]["image"] = $relatedImage->getUrl();
					}
			    }
			}
			
			    
			$collection = $this->_reviewCollection->create();
			$collection->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED);
			$collection->addEntityFilter('product', $product->getId())->setDateOrder();
			$reviews = $collection->getData();
			
			$this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
    		$ratingSummary = $product->getRatingSummary()->getRatingSummary();
    		$current_product['ratings'] = $ratingSummary;
    		
			if(count($reviews) > 0) {
				foreach($reviews as $k => $review) {
					$current_product['reviews'][$k]["reviewId"] = $review['review_id'];
					$current_product['reviews'][$k]["customerId"] = $review['customer_id'];
					$current_product['reviews'][$k]["title"] = $review['title'];
					$current_product['reviews'][$k]["detail"] = $review['detail'];
					$current_product['reviews'][$k]["nickname"] = $review['nickname'];
				}
			}
			
			$current_product['reviewForm'][0]["ratingPrice"] = "radio";
			$current_product['reviewForm'][0]["ratingValue"] = "radio";
			$current_product['reviewForm'][0]["ratingQuality"] = "radio";
			$current_product['reviewForm'][0]["nickname"] = "textbox";
			$current_product['reviewForm'][0]["summary"] = "textbox";
			$current_product['reviewForm'][0]["review"] = "textarea";
		
		}
		return $current_product;
    }
}

