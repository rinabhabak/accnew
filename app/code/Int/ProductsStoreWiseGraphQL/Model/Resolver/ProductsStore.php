<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ProductsStoreWiseGraphQL\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Registry;

class ProductsStore implements ResolverInterface
{

    private $productsStoreDataProvider;
    private $registry;

    /**
     * @param DataProvider\ProductsStore $productsStoreRepository
     */
    public function __construct(
        DataProvider\ProductsStore $productsStoreDataProvider,
        Registry $registry
    ) {
        $this->productsStoreDataProvider = $productsStoreDataProvider;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
    	$category = $this->registry->registry('current_category');
    	if(count($args) > 0) {
    		if(array_key_exists('storeId',$args)) {
				$store_id = $args['storeId'];
			} else {
				$store_id = 1;
			}
    		if(array_key_exists('productsPerPage',$args)) {
				$products_per_page = $args['productsPerPage'];
			} else {
				$products_per_page = 20;
			}
			if(array_key_exists('pageNumber',$args)) {
    			$page_number = $args['pageNumber'];
    		} else {
				$page_number = 1;
			}
			if(array_key_exists('categoryId',$args)) {
    			$category_id = $args['categoryId'];
    		} else {
				$category_id = $category ? $category->getId() : 0;
			}
			if(array_key_exists('sortBy',$args)) {
				$sort_by = $args['sortBy'];
			} else {
				$sort_by = 'position';
			}
			if(array_key_exists('sortOrder',$args)) {
				$sort_order = $args['sortOrder'];
			} else {
				$sort_order = 'asc';
			}
			if(array_key_exists('filters',$args)) {
				$filters = $args['filters'];
			} else {
				$filters = '';
			}
        } else {
			$store_id = 1;
			$products_per_page = 20;
			$page_number = 1;
			$category_id = $category ? $category->getId() : 0;
			$sort_by = 'position';
			$sort_order = 'asc';
			$filters = '';
		}
        $productsStoreData = $this->productsStoreDataProvider->getProductsStore($store_id,$category_id,$products_per_page,$page_number,$sort_by,$sort_order,$filters);
        return $productsStoreData;
    }
}

