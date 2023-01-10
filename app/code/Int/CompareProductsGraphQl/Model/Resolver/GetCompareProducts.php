<?php
/**
 * Copyright © Int, Inc. All rights reserved.
 */
declare(strict_types=1);

namespace Int\CompareProductsGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Authorization\Model\UserContextInterface;

class GetCompareProducts implements ResolverInterface
{

    private $getCompareProductsDataProvider;
    private $customerVisitor;

    /**
     * @param \Magento\Customer\Model\Visitor $customerVisitor,
     * @param \Int\CompareProductsGraphQl\Model\Resolver\DataProvider\GetCompareProducts $getCompareProductsRepository
     */
    public function __construct(
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Int\CompareProductsGraphQl\Model\Resolver\DataProvider\GetCompareProducts $getCompareProductsDataProvider
    ) {
        $this->getCompareProductsDataProvider = $getCompareProductsDataProvider;
        $this->customerVisitor = $customerVisitor;
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
        if ( false === $context->getExtensionAttributes()->getIsCustomer()) {
            //$customer_id = $this->customerVisitor->getId();
            throw new GraphQlAuthorizationException(
                __('The current customer isn\'t authorized.')
            );
        }

        $customer_id = (int) $context->getUserId();
        $store_id = (int) $context->getExtensionAttributes()->getStore()->getId();

        $getCompareProductsData = $this->getCompareProductsDataProvider->getItems($customer_id, $store_id);
        return $getCompareProductsData;
    }
}
