<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\CartGraphQL\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CartDetails implements ResolverInterface
{

    private $cartDetailsDataProvider;

    /**
     * @param DataProvider\CartDetails $cartDetailsRepository
     */
    public function __construct(
        DataProvider\CartDetails $cartDetailsDataProvider
    ) {
        $this->cartDetailsDataProvider = $cartDetailsDataProvider;
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
        $cartDetailsData = $this->cartDetailsDataProvider->getCartDetails();
        return $cartDetailsData;
    }
}

