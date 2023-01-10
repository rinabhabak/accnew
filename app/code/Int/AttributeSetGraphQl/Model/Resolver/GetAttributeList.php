<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\AttributeSetGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetAttributeList implements ResolverInterface
{

    private $getAttributeListDataProvider;

    /**
     * @param DataProvider\GetAttributeList $getAttributeListRepository
     */
    public function __construct(
        DataProvider\GetAttributeList $getAttributeListDataProvider
    ) {
        $this->getAttributeListDataProvider = $getAttributeListDataProvider;
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
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(
                __('The current customer isn\'t authorized.')
            );
        }

        $getAttributeListData = $this->getAttributeListDataProvider->getGetAttributeList();
        return ['attributes' => $getAttributeListData];
    }
}

