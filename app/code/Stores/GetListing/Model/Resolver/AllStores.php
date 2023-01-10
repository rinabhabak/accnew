<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Stores\GetListing\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class AllStores implements ResolverInterface
{

    private $allStoresDataProvider;

    /**
     * @param DataProvider\AllStores $allStoresRepository
     */
    public function __construct(
        DataProvider\AllStores $allStoresDataProvider
    ) {
        $this->allStoresDataProvider = $allStoresDataProvider;
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
			$allStoresData = $this->allStoresDataProvider->getStoreById($store_id);
		} else {
			$allStoresData = $this->allStoresDataProvider->getAllStores();
		}
		return $allStoresData;
    }
}

