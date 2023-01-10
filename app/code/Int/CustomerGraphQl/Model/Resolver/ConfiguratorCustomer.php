<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Newsletter\Model\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * customer account resolver
 */
class ConfiguratorCustomer implements ResolverInterface
{
    protected $_customerRepositoryInterface;

    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @var Config
     */
    private $newsLetterConfig;

    /**
     * CreateCustomer constructor.
     *
     * @param ExtractCustomerData $extractCustomerData
     * @param CreateCustomerAccount $createCustomerAccount
     * @param Config $newsLetterConfig
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        ExtractCustomerData $extractCustomerData,
        Config $newsLetterConfig
    ) {
        $this->newsLetterConfig = $newsLetterConfig;
        $this->extractCustomerData = $extractCustomerData;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
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
        if(!isset($args['customer_id']) || empty($args['customer_id'])){
            throw new GraphQlInputException(__('Customer Id is required.'));
        }
        
        $customer = $this->_customerRepositoryInterface->getById($args['customer_id']);        
        if(!$customer->getId()){
            throw new GraphQlInputException(__('Invalid customer id.'));
        }

        $data = $this->extractCustomerData->execute($customer);
        return ['customer' => $data];
    }
}
