<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\CreateCustomerAccount;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Newsletter\Model\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * Create customer account resolver
 */
class CreateCustomer extends \Magento\CustomerGraphQl\Model\Resolver\CreateCustomer
{
    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @var CreateCustomerAccount
     */
    private $createCustomerAccount;

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
        ExtractCustomerData $extractCustomerData,
        CreateCustomerAccount $createCustomerAccount,
        Config $newsLetterConfig
    ) {
        $this->newsLetterConfig = $newsLetterConfig;
        $this->extractCustomerData = $extractCustomerData;
        $this->createCustomerAccount = $createCustomerAccount;
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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog2.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($args['input']['email']);

        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }
        
        if (!isset($args['input']['identifier']) && !isset($args['input']['customer'])) {
            throw new GraphQlInputException(__('"Identifier" and "Customer" value should be specified'));
        }else{
            $identifier = $args['input']['identifier'];
            $customer = $args['input']['customer'];

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templogCreate.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($args['input']['customer']);

            if(md5(md5($customer))!==$identifier){
                throw new GraphQlInputException(__('Unauthorize access.'));
            }
        }

        if (!$this->newsLetterConfig->isActive(ScopeInterface::SCOPE_STORE)) {
            $args['input']['is_subscribed'] = false;
        }
        if (isset($args['input']['date_of_birth'])) {
            $args['input']['dob'] = $args['input']['date_of_birth'];
        }

        if (isset($args['input']['email'])) {
            $args['input']['email'] = base64_decode($args['input']['email']);
        }
        if (isset($args['input']['password'])) {
            $args['input']['password'] = base64_decode($args['input']['password']);
        }

        $customer = $this->createCustomerAccount->execute(
            $args['input'],
            $context->getExtensionAttributes()->getStore()
        );
        $data = $this->extractCustomerData->execute($customer);

        $data['id'] = $customer->getId();
        return ['customer' => $data];
    }
}
