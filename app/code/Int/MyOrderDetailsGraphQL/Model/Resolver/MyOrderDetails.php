<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\MyOrderDetailsGraphQL\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class MyOrderDetails implements ResolverInterface
{

    private $myOrderDetailsDataProvider;

    /**
     * @param DataProvider\MyOrderDetails $myOrderDetailsRepository
     */
    public function __construct(
        DataProvider\MyOrderDetails $myOrderDetailsDataProvider
    ) {
        $this->myOrderDetailsDataProvider = $myOrderDetailsDataProvider;
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
    		if(array_key_exists('orderId',$args)) {
    			$order_id = $args['orderId'];
    		} else {
				$order_id = 0;
			}
    	}  else {
			$order_id = 0;
		}
        $myOrderDetailsData = $this->myOrderDetailsDataProvider->getMyOrderDetails($order_id);
        return $myOrderDetailsData;
    }
}

