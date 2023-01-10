<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ProductDetailGraphQL\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ProductDetail implements ResolverInterface
{

    private $productDetailDataProvider;

    /**
     * @param DataProvider\ProductDetail $productDetailRepository
     */
    public function __construct(
        DataProvider\ProductDetail $productDetailDataProvider
    ) {
        $this->productDetailDataProvider = $productDetailDataProvider;
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
    		if(array_key_exists('productId',$args)) {
    			$product_id = $args['productId'];
    		} else {
				$product_id = 0;
			}
    	}  else {
			$product_id = 0;
		}
        $productDetailData = $this->productDetailDataProvider->getProductDetail($product_id);
        return $productDetailData;
    }
}

