<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ProductFiltersGraphQL\Model\Resolver\DataProvider;

class ProductFilters
{
	protected $_layerResolver;
	
    public function __construct(
        \Magento\Catalog\Model\Layer\Resolver $layerResolver
    ) {
        $this->_layerResolver = $layerResolver;
    }

    public function getProductFilters($category_id)
    {
    	
	    $layer = $this->_layerResolver->get();
	    $activeFilters = $layer->getState()->getFilters();
	    //var_dump($activeFilters);
	    foreach($activeFilters as $i => $activeFilter) {
	           $activeFilterName = (string)$activeFilter->getName();
	           //echo $activeFilterName."<br />";
	    }
    	
        $productFilters = [];
        
        if($category_id > 0) {
        	$category = $category_id;
        	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

	        $filterableAttributes = $objectManager->getInstance()->get(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList::class);

	        $appState = $objectManager->getInstance()->get(\Magento\Framework\App\State::class);
	        $layerResolver = $objectManager->getInstance()->get(\Magento\Catalog\Model\Layer\Resolver::class);
	        $filterList = $objectManager->getInstance()->create(
	            \Magento\Catalog\Model\Layer\FilterList::class,
	                [
	                    'filterableAttributes' => $filterableAttributes
	                ]
	            );      

	        $layer = $layerResolver->get();
	        $layer->setCurrentCategory($category);
	        $filters = $filterList->getFilters($layer);
	        /*
	        $maxPrice = $layer->getProductCollection()->getMaxPrice();
	        $minPrice = $layer->getProductCollection()->getMinPrice();
	        */
	        
	        foreach($filters as $i => $filter) {
	           if ($filter->getItemsCount()) {
	           	   $availablefilter = (string)$filter->getName();
		           $items = $filter->getItems();
		           $productFilters['availableFilters'][$i]['attributeCode'] = $filter->getRequestVar();
		           $productFilters['availableFilters'][$i]['attributeName'] = $availablefilter;
		           foreach($items as $j => $item)
		           {
		               $productFilters['availableFilters'][$i]['filters'][$j]['attributeDisplayText'] = strip_tags($item->getLabel());
		               $productFilters['availableFilters'][$i]['filters'][$j]['attributeValue'] = $item->getValue();
		               $productFilters['availableFilters'][$i]['filters'][$j]['attributeValueCount'] = $item->getCount();
		           }
	           }
	       	}
        }
        
        return $productFilters;
    }
}

