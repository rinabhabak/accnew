<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Products\StoreWiseGraphQL\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ProductsStore implements ResolverInterface
{

    private $productsStoreDataProvider;

    /**
     * @param DataProvider\ProductsStore $productsStoreRepository
     */
    public function __construct(
        DataProvider\ProductsStore $productsStoreDataProvider
    ) {
        $this->productsStoreDataProvider = $productsStoreDataProvider;
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
    	if(count($args) > 0) {
    		$store_id = $args['store_id'];
        } else {
			$store_id = 1;
		}
        $productsStoreData = $this->productsStoreDataProvider->getProductsStore($store_id);
        return $productsStoreData;
    }
}

