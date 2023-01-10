<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\MiniCartGraphQL\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class MiniCart implements ResolverInterface
{
    private $miniCartDataProvider;
    /**
     * @param DataProvider\MiniCart $miniCartRepository
     */
    public function __construct(
        DataProvider\MiniCart $miniCartDataProvider
    ) {
        $this->miniCartDataProvider = $miniCartDataProvider;
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
        $miniCartData = $this->miniCartDataProvider->getMiniCart();
        return $miniCartData;
    }
}
