<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ProductFiltersGraphQL\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Registry;

class ProductFilters implements ResolverInterface
{

    private $productFiltersDataProvider;
    private $registry;

    /**
     * @param DataProvider\ProductFilters $productFiltersRepository
     */
    public function __construct(
        DataProvider\ProductFilters $productFiltersDataProvider,
        Registry $registry
    ) {
        $this->productFiltersDataProvider = $productFiltersDataProvider;
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
			if(array_key_exists('categoryId',$args)) {
    			$category_id = $args['categoryId'];
    		} else {
				$category_id = $category ? $category->getId() : 2;
			}
        } else {
			$category_id = $category ? $category->getId() : 2;
		}
        $productFiltersData = $this->productFiltersDataProvider->getProductFilters($category_id);
        return $productFiltersData;
    }
}

