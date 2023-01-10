<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ConfiguratorGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetConfigurator implements ResolverInterface
{

    private $getConfiguratorDataProvider;

    /**
     * @param DataProvider\GetConfigurator $getConfiguratorRepository
     */
    public function __construct(
        DataProvider\GetConfigurator $getConfiguratorDataProvider
    ) {
        $this->getConfiguratorDataProvider = $getConfiguratorDataProvider;
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
                __('Your session has been expired. Please log in again.')
            );
        }

        try{
            $customerId = isset($args['customer_id']) ? $args['customer_id']:$context->getUserId();
            $getConfiguratorData = $this->getConfiguratorDataProvider->getGetConfigurator($customerId);
            return [
                'configurator' => $getConfiguratorData
            ];
            
        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}

