<?php
namespace Int\CompanyGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\Math\Random;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Api\CompanyRepositoryInterface;


class CompanyTokenGeneration implements ResolverInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;


    /**
     * Customer registry.
     *
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $_customerRegistry;

    /**
     * Math Random.
     *
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * Magento DateTimeFactory.
     *
     * @var \Magento\Framework\Intl\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * ScopeConfigInterface.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $timezone;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var CompanyManagementInterface
     */
    private $companyManagement;

    
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        Random $mathRandom,
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        CompanyRepositoryInterface $companyRepository,
        CompanyManagementInterface $companyManagement
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_customerRegistry   = $customerRegistry;
        $this->mathRandom = $mathRandom;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;
        $this->companyRepository = $companyRepository;
        $this->companyManagement = $companyManagement;
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
    ){
        try {

            if (!isset($args['input']['company_customer_email']) || empty($args['input']['company_customer_email'])) {
                throw new GraphQlInputException(__('"company_customer_email" value should be specified'));
            }

            $customerData = [
                "company_customer_email" => $args['input']['company_customer_email']
            ];

            $companyEmail = $args['input']['company_customer_email'];
            try{
                $customer = $this->_customerRepositoryInterface->get($companyEmail, $websiteId = null);
                $companyId = $this->companyManagement->getByCustomerId($customer->getId());
                if($companyId){
                    $customerSecure = $this->_customerRegistry->retrieveSecureData($customer->getId());
                    
                    if (!$customerSecure->getRpToken()) {
                        throw new GraphQlInputException(__('Already password generated, please login with existing password.'));
                    }
                    
                    $expireTime = $this->scopeConfig->getValue('customer/password/reset_link_expiration_period', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
                    $timestamp = strtotime($customerSecure->getRpTokenCreatedAt()) + 60*60*$expireTime;
                    $time = date('Y-m-d H:i:s', $timestamp);
                    $response = array();
                    $response['company_token'] = $customerSecure->getRpToken();
                    return $response;
                }
                
            } catch (\Exception $e) {
                throw new GraphQlInputException(__('No account found with the email address, Please re-check and try again.'));
            }
            
        } catch (\Exception $e) {
            throw new GraphQlInputException(__('No account found with the email address, Please re-check and try again.'));
        }
        

    }
}