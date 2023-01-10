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
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

class GetSavedConfiguratorsData implements ResolverInterface
{
    /**
     * @var DataProvider\GetSavedConfiguratorsData
     */
    private $_getSavedConfiguratorsData;

    /**
     * @param DataProvider\GetSavedConfiguratorsData $getSavedConfiguratorsDataDataProvider
     */
    public function __construct(
        DataProvider\GetSavedConfiguratorsData $getSavedConfiguratorsDataDataProvider
    ) {
        $this->_getSavedConfiguratorsData = $getSavedConfiguratorsDataDataProvider;
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
            $customerId = (int) $context->getUserId();
			$no_of_configurator = isset($args['no_of_configurator']) ? $args['no_of_configurator']:5;
            $getSavedConfiguratorsData = $this->_getSavedConfiguratorsData->getGetSavedConfiguratorsData($customerId,$no_of_configurator);
            return [
                'last_saved_list' => $getSavedConfiguratorsData
            ];

        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}