<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\CustomerGraphQl\Model\Customer\UpdateCustomerAccount;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerPassword;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;


/**
 * Update customer data resolver
 */
class UpdateCustomer extends \Magento\CustomerGraphQl\Model\Resolver\UpdateCustomer 
{
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var UpdateCustomerAccount
     */
    private $updateCustomerAccount;

    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $_customer;

    protected $customerRepository;


    /**
     * @param GetCustomer $getCustomer
     * @param UpdateCustomerAccount $updateCustomerAccount
     * @param ExtractCustomerData $extractCustomerData
     */
    public function __construct(
        GetCustomer $getCustomer,
        UpdateCustomerAccount $updateCustomerAccount,
        ExtractCustomerData $extractCustomerData,
         \Magento\Framework\ObjectManagerInterface $objectmanager,
         CheckCustomerPassword $checkCustomerPassword,
         AccountManagementInterface $accountManagement,
         \Magento\Customer\Model\Customer $customer,
         \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
         \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->getCustomer = $getCustomer;
        $this->updateCustomerAccount = $updateCustomerAccount;
        $this->extractCustomerData = $extractCustomerData;
        $this->objectmanager = $objectmanager;
        $this->checkCustomerPassword = $checkCustomerPassword;
        $this->accountManagement = $accountManagement;
        $this->_customer = $customer;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
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



        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        //echo 99;die;
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }
        if (isset($args['input']['date_of_birth'])) {
            $args['input']['dob'] = $args['input']['date_of_birth'];
        }
        $customer = $this->getCustomer->execute($context);

        if (isset($args['input']['updatePassword']) && !isset($args['input']['updateEmail'])) {
            
            if (!isset($args['input']['updatePassword']['currentPassword']) || '' == trim($args['input']['updatePassword']['currentPassword'])) {
                throw new GraphQlInputException(__('Specify the "currentPassword" value.'));
            }
            
            if (!isset($args['input']['updatePassword']['newPassword']) || '' == trim($args['input']['updatePassword']['newPassword'])) {
                    throw new GraphQlInputException(__('Specify the "newPassword" value.'));
            }
            
            $customerId = $context->getUserId();
            $_customerCurrentPassword = base64_decode($args['input']['updatePassword']['currentPassword']);
            $_customerNewPassword = base64_decode($args['input']['updatePassword']['newPassword']);
            
            try {
                
                try{
                    $this->checkCustomerPassword->execute($_customerCurrentPassword, $customerId);
                } catch(AuthenticationException $e) {
                    throw new GraphQlAuthenticationException(__('Please enter valid old/current password.'));
                }
                
                $this->accountManagement->changePasswordById($customerId, $_customerCurrentPassword, $_customerNewPassword);
                
            } catch (LocalizedException $e) {
                throw new GraphQlInputException(__($e->getMessage()), $e);
            }
        }
        

        
        
        if (isset($args['input']['updateEmail']) && (!isset($args['input']['updatePassword']))) {
            
            if (!isset($args['input']['updateEmail']['email']) || '' == trim($args['input']['updateEmail']['email'])) {
                throw new GraphQlInputException(__('Specify the "email" value.'));
            }
            
            if (!isset($args['input']['updateEmail']['currentPassword']) || '' == trim($args['input']['updateEmail']['currentPassword'])) {
                throw new GraphQlInputException(__('Specify the "currentPassword" value.'));
            }
            
            $customerId = $context->getUserId();
            $_customerUpdateEmailPassword = base64_decode($args['input']['updateEmail']['currentPassword']);
            
            $this->checkCustomerPassword->execute($_customerUpdateEmailPassword, $customerId);
            $websiteId =$this->storeManager->getStore()->getWebsiteId();
            $loadCustomerByEmail = $this->_customer->setWebsiteId( $websiteId)->loadByEmail($args['input']['updateEmail']['email'],$context->getExtensionAttributes()->getStore());

            if ($loadCustomerByEmail->getId()) {
                 throw new GraphQlInputException(__('Please try another Email address.'));
            }else {
                // load customer info by id
                $customerId = $context->getUserId();
                $customer = $this->customerRepository->getById($customerId);
                $customer->setEmail($args['input']['updateEmail']['email']);
                try{
                    $this->customerRepository->save($customer);
                } catch (LocalizedException $e) {
                    throw new GraphQlInputException(__($e->getMessage()), $e);
                }
                
            }
        }
        
        
        

        if((isset($args['input']['updateEmail'])) && (isset($args['input']['updatePassword'])))
        {
            $checkemail = true;
            $checkpassword = true;
            
            if (!isset($args['input']['updatePassword']['currentPassword']) || '' == trim($args['input']['updatePassword']['currentPassword'])) {
                throw new GraphQlInputException(__('Specify the "currentPassword" value.'));
            }
            
            if (!isset($args['input']['updatePassword']['newPassword']) || '' == trim($args['input']['updatePassword']['newPassword'])) {
                throw new GraphQlInputException(__('Specify the "newPassword" value.'));
            }

            $customerId = $context->getUserId();
            $_customerCurrentPassword = base64_decode($args['input']['updatePassword']['currentPassword']);
            $_customerNewPassword = base64_decode($args['input']['updatePassword']['newPassword']);
            
        
            try{
                $this->checkCustomerPassword->execute($_customerCurrentPassword, $customerId);
            }catch(AuthenticationException $e) {
                throw new GraphQlAuthenticationException(__('Please enter valid old/current password.'));
            }
            
            if (!isset($args['input']['updateEmail']['email']) || '' == trim($args['input']['updateEmail']['email'])) {
                throw new GraphQlInputException(__('Specify the "email" value.'));
            }

            if (!isset($args['input']['updateEmail']['currentPassword']) || '' == trim($args['input']['updateEmail']['currentPassword'])) {
                throw new GraphQlInputException(__('Specify the "currentPassword" value.'));
            }

            $websiteId =$this->storeManager->getStore()->getWebsiteId();
            $loadCustomerByEmail = $this->_customer->setWebsiteId( $websiteId)->loadByEmail($args['input']['updateEmail']['email'],$context->getExtensionAttributes()->getStore());

            if ($loadCustomerByEmail->getId()) {
                 throw new GraphQlInputException(__('Please try another Email address.'));
            }else {
                // load customer info by id
                $customerId = $context->getUserId();
                $customer = $this->customerRepository->getById($customerId);
                $customer->setEmail($args['input']['updateEmail']['email']);
                try{
                    $this->accountManagement->changePasswordById($customerId, $_customerCurrentPassword, $_customerNewPassword);
                    $this->customerRepository->save($customer);
                } catch (LocalizedException $e) {
                    throw new GraphQlInputException(__($e->getMessage()), $e);
                }
            }
        }
        
        $job_title_model =  $this->objectmanager->create('Magento\Company\Model\Customer');
        $job_title_model->setCustomerId($customer->getId());
        if(array_key_exists("jobTitle", $args['input'])){
            $job_title_model->setJobTitle($args['input']['jobTitle']);
        }
        
        $this->updateCustomerAccount->execute(
            $customer,
            $args['input'],
            $context->getExtensionAttributes()->getStore()
        );
            
        $data = $this->extractCustomerData->execute($customer);
        $job_title_model->save();
        return ['customer' => $data];
    }
}
