<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_CustomerGraphQl
 * @author    Indusnet
 */

namespace Int\CustomerGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;


/**
 * Class GetCustomerCompany
 * @package Int\CustomerGraphQl\Model\Resolver
 */
class GetCustomerCompany implements ResolverInterface
{
   
    /**
* @var \Magento\Directory\Model\RegionFactory
*/
    protected $_regionFactory;
    protected $_customerFactory;
    /**
     * @var GetCustomer
     */
    private $getCustomer;
    protected $_paymentMethod;


     public function __construct(
        \Magento\Company\Api\CompanyManagementInterface $companyRepository,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Company\Block\Company\CompanyProfile $companyProfile,
        GetCustomer $getCustomer,
        \Magento\CompanyPayment\Block\Company\Profile\PaymentMethod $PaymentMethod
    ) {
       $this->companyRepository = $companyRepository;
       $this->_countryFactory = $countryFactory;
       $this->_regionFactory = $regionFactory;
       $this->_customerFactory = $customerFactory;
       $this->_companyProfile = $companyProfile;
       $this->getCustomer = $getCustomer;
       $this->_paymentMethod = $PaymentMethod;
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
        	$customer = $this->getCustomer->execute($context);
            $customerId = $context->getUserId();
            $street = '';
            $company = $this->companyRepository->getByCustomerId($customerId);
            $country = $this->_countryFactory->create()->loadByCode($company->getCountryId());
            $region = $this->_regionFactory->create();
            $regionData = $region->load($company->getRegionId());
            if (!empty($company->getStreet()))
                $street = $this->_companyProfile->getCompanyStreetLabel($company);
           

            $customer = $this->_customerFactory->create()->load($company->getSuperUserId());  
            $customerCategory = [];
            if(count($this->_paymentMethod->getPaymentMethods()) >0)
            {
            	$i = 0;
            	foreach($this->_paymentMethod->getPaymentMethods() as $paymentMethods){
            		$customerCategory['getCustomerCompany']['paymentMethods'][$i]['method_name'] = $paymentMethods;
            		$i++;
            	}
            }
            
            $customerCategory['getCustomerCompany']['company_name'] = $company->getCompanyName();
            $customerCategory['getCustomerCompany']['legal_name'] = $company->getLegalName();
            $customerCategory['getCustomerCompany']['company_email'] = $company->getCompanyEmail();
            $customerCategory['getCustomerCompany']['vat_tax_id'] = $company->getVatTaxId();
            $customerCategory['getCustomerCompany']['reseller_id'] = $company->getResellerId();
            $customerCategory['getCustomerCompany']['comment'] = $company->getComment();
            $customerCategory['getCustomerCompany']['street'] = $this->_companyProfile->getCompanyStreetLabel($company);
            $customerCategory['getCustomerCompany']['city'] = $company->getCity();
            $customerCategory['getCustomerCompany']['country_name'] = $country->getName();
            $customerCategory['getCustomerCompany']['region'] = $company->getRegion();
            $customerCategory['getCustomerCompany']['region_name'] = $regionData->getName();
            $customerCategory['getCustomerCompany']['postcode'] = $company->getPostcode();
            $customerCategory['getCustomerCompany']['telephone'] = $company->getTelephone();
            $customerCategory['getCustomerCompany']['administrator_name'] = $this->_companyProfile->getCompanyAdminName($company);
            $customerCategory['getCustomerCompany']['job_title'] = $this->_companyProfile->getCompanyAdminJobTitle($company);
            $customerCategory['getCustomerCompany']['email'] = $this->_companyProfile->getCompanyAdminEmail($company);
            $customerCategory['getCustomerCompany']['sales_representative_name'] = $this->_companyProfile->getSalesRepresentativeName($company);
            $customerCategory['getCustomerCompany']['sales_representative_email'] = $this->_companyProfile->getSalesRepresentativeEmail($company);

        return $customerCategory;

    }

}