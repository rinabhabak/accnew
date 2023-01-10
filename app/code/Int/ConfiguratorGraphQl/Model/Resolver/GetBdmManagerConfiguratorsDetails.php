<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ConfiguratorGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetBdmManagerConfiguratorsDetails implements ResolverInterface
{

    private $getConfiguratorDataProvider;

    /**
     * @param DataProvider\GetConfigurator $getConfiguratorRepository
     */
    public function __construct(
        DataProvider\GetBdmManagerConfiguratorsDetails $getConfiguratorDataProvider
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

        if (empty($args['configurator_id'])) {
            throw new GraphQlInputException(
                __('Configurator id is required.')
            );
        }

        $configurator_id = (int) $args['configurator_id'];

        try{
            $configuratorData = $this->getConfiguratorDataProvider->getGetConfiguratorData($configurator_id);
            return  $configuratorData;
            
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }
    }
}

