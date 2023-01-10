<?php
/**
 * Copyright © INT_CustomerGraphQL, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Int\CustomerGraphQl\Model\Rewrite;

use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\CredentialsValidator;
use Magento\Integration\Model\Oauth\Token as Token;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Integration\Model\CustomerTokenService as BaseCustomerTokenService;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Model\ResourceModel\Company\CollectionFactory as CompanyCollectionFactory;

/**
 * @inheritdoc
 */
class CustomerTokenService extends BaseCustomerTokenService
{
	/**
     * Token Model
     *
     * @var TokenModelFactory
     */
    private $tokenModelFactory;

    /**
     * @var Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * Customer Account Service
     *
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var \Magento\Integration\Model\CredentialsValidator
     */
    private $validatorHelper;

    /**
     * Token Collection Factory
     *
     * @var TokenCollectionFactory
     */
    private $tokenModelCollectionFactory;

    /**
     * @var RequestThrottler
     */
    private $requestThrottler;

    /**
     * @var CompanyCollectionFactory
     */
    private $companyCollection;

    /**
     * Initialize service
     *
     * @param TokenModelFactory $tokenModelFactory
     * @param AccountManagementInterface $accountManagement
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     * @param \Magento\Integration\Model\CredentialsValidator $validatorHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param CompanyCollectionFactory $companyCollection
     */
    public function __construct(
        TokenModelFactory $tokenModelFactory,
        AccountManagementInterface $accountManagement,
        TokenCollectionFactory $tokenModelCollectionFactory,
        CredentialsValidator $validatorHelper,
        ManagerInterface $eventManager = null,
        CompanyCollectionFactory $companyCollection
    ) {
        $this->tokenModelFactory = $tokenModelFactory;
        $this->accountManagement = $accountManagement;
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->validatorHelper = $validatorHelper;
        $this->companyCollection = $companyCollection;
        $this->eventManager = $eventManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ManagerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function createCustomerAccessToken($username, $password)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        
        $username =  base64_decode($username);
        $password =  base64_decode($password);

        $logger->info($username);
        $logger->info($password);

        $this->validatorHelper->validate($username, $password);
        $this->getRequestThrottler()->throttle($username, RequestThrottler::USER_TYPE_CUSTOMER);
        
        try {
            $customerDataObject = $this->accountManagement->authenticate($username, $password);
        } catch (\Exception $e) {
            $this->getRequestThrottler()->logAuthenticationFailure($username, RequestThrottler::USER_TYPE_CUSTOMER);
            throw new AuthenticationException(
                __('Sorry the Email or Password don\'t match our records. Please try again or Create an Account')
            );
        }

        $this->checkCompanyStatus($customerDataObject->getId());
        $this->eventManager->dispatch('customer_login', ['customer' => $customerDataObject]);
        $this->getRequestThrottler()->resetAuthenticationFailuresCount($username, RequestThrottler::USER_TYPE_CUSTOMER);
        return $this->tokenModelFactory->create()->createCustomerToken($customerDataObject->getId())->getToken();
    }

    /**
     * Get request throttler instance
     *
     * @return RequestThrottler
     * @deprecated 100.0.4
     */
    private function getRequestThrottler()
    {
        if (!$this->requestThrottler instanceof RequestThrottler) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(RequestThrottler::class);
        }
        return $this->requestThrottler;
    }

    /**
     * Check Company Status By Customer ID 
     *
     * @return AuthenticationException | null
     */
    private function checkCompanyStatus($customer_id = null)
    {
        if(is_null($customer_id)){
            throw new AuthenticationException(
                __('Customer not exists. Please contact seller')
            );
        }

        $_companyCollection = $this->companyCollection->create()
                            ->addFieldToFilter('super_user_id', $customer_id)
                            ->load()->getFirstItem();

        if(!is_null($_companyCollection->getId()))
        {
            if($_companyCollection->getStatus() == 0){
                throw new AuthenticationException(
                    __('Your account is not yet approved. If you have questions, Please contact the seller.')
                );
            }

            if($_companyCollection->getStatus() == 2){
                throw new AuthenticationException(
                    __('This account is locked.')
                );
            }

            if($_companyCollection->getStatus() == 3){
                throw new AuthenticationException(
                    __('Your company account is blocked and you cannot place orders. If you have questions, please contact your company administrator.')
                );
            }
        }
    }
}