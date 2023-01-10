<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\CustomerGraphQl\Model\Resolver;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Customer\Api\AccountManagementInterface;


/**
 * Customers Token resolver, used for GraphQL request processing.
 */
class GenerateCustomerToken extends \Magento\CustomerGraphQl\Model\Resolver\GenerateCustomerToken
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected $groupRepository;

    protected $customerRepository;

    protected $_customerSession;
    protected $_localeDate;

    /**
     * Customer Account Service
     *
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * Customer log
     *
     * @var \Magento\Customer\Model\Log
     */
    protected $customerLog;

    
    /**
     * Customer logger
     *
     * @var \Magento\Customer\Model\Logger
     */
    protected $customerLogger;

    /**
     * @param CustomerTokenServiceInterface $customerTokenService
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Model\Logger $customerLogger
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(
        CustomerTokenServiceInterface $customerTokenService,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        AccountManagementInterface $accountManagement,
        \Magento\Customer\Model\Logger $customerLogger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->customerTokenService = $customerTokenService;
        $this->groupRepository = $groupRepository;
        $this->customerRepository = $customerRepository;
        $this->_customerSession = $customerSession;
        $this->accountManagement = $accountManagement;
        $this->customerLogger = $customerLogger;
        $this->_localeDate = $localeDate;
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
        if (empty($args['email'])) {
            throw new GraphQlInputException(__('Specify the "email" value.'));
        }

        if (empty($args['password'])) {
            throw new GraphQlInputException(__('Specify the "password" value.'));
        }

        try {
            $token = $this->customerTokenService->createCustomerAccessToken($args['email'], $args['password']);
            $username =  base64_decode($args['email']);
            $password =  base64_decode($args['password']);
            
            try {
                $_customer = $this->accountManagement->authenticate($username, $password);
                $_customerId = $_customer->getId();
            } catch (\Exception $e) {
                $this->getRequestThrottler()->logAuthenticationFailure($username, RequestThrottler::USER_TYPE_CUSTOMER);
                throw new AuthenticationException(
                    __('Sorry the Email or Password don\'t match our records. Please try again or Create an Account.')
                );
            }
            
            $lastLogin = $this->getCustomerLastLoginDate($_customerId);
            $isFirstLogin = $this->isCustomerFirstLogin($_customerId);
            
            //Log customer login History
            $this->customerLogger->log($_customerId, array('last_login_at'=>date('Y-m-d h:s:i'), 'last_logout_at'=>NULL));
            
            // Return customer data
            
            return [
                'token' => $token,
                'customer_id' => base64_encode($_customer->getId()),
                'firstname' => $_customer->getFirstname(),
                'lastname' => $_customer->getLastname(),                
                'customerGroup' => $this->getGroupName($_customer->getGroupId()),
                'lastLogin' => $lastLogin,
                'isFirstLogin' => $isFirstLogin                
            ];
            
        } catch (AuthenticationException $e) {
            throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
        }
    }
    
    
    
    /**
     * Retrieves customer group id
     * @return integer $customerGroupId
     */

    public function getGroupId(){
        if($this->_customerSession->isLoggedIn()):
            return $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        endif;
    }
    
    
    /**
     * Retrieves customer group name
     * @param integer $groupId
     * @return string 
     */

    public function getGroupName($groupId){
        $group = $this->groupRepository->getById($groupId);
        return $group->getCode();
    }
    
    
    /**
     * Retrieves customer log model
     *
     * @return \Magento\Customer\Model\Log
     */
    protected function getCustomerLog($customerId)
    {
        if (!$this->customerLog) {
            $this->customerLog = $this->customerLogger->get($customerId);
        }

        return $this->customerLog;
    }
    
    
    /**
     * Get customer last login date.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getCustomerLastLoginDate($customerId)
    {
        $date = $this->getCustomerLog($customerId)->getLastLoginAt();

        if ($date) {
            return $this->formatDate($date, \IntlDateFormatter::MEDIUM, true);
        }

        return __('Never');
    }
    
    
    /**
     * Check if customer login for the first time.
     *
     * @return boolean
     */
    public function isCustomerFirstLogin($customerId)
    {
        $date = $this->getCustomerLog($customerId)->getLastLoginAt();
        if ($date) {
            return false;
        }
        return true;
    }
    
    
    /**
     * Retrieve formatting date
     *
     * @param null|string|\DateTimeInterface $date
     * @param int $format
     * @param bool $showTime
     * @param null|string $timezone
     * @return string
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }
    
    
}
