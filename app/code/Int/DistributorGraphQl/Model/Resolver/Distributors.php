<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_DistributorGraphQl
 * @author    Indusnet
 */

namespace Int\DistributorGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;


/**
 * Class Distributors
 * @package Int\DistributorGraphQl\Model\Resolver
 */
class Distributors implements ResolverInterface
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

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;


     public function __construct(
        \Magento\Company\Api\CompanyManagementInterface $companyRepository,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Company\Block\Company\CompanyProfile $companyProfile,
        GetCustomer $getCustomer,
        \Magento\CompanyPayment\Block\Company\Profile\PaymentMethod $PaymentMethod,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
       $this->companyRepository = $companyRepository;
       $this->_countryFactory = $countryFactory;
       $this->_regionFactory = $regionFactory;
       $this->_customerFactory = $customerFactory;
       $this->_companyProfile = $companyProfile;
       $this->getCustomer = $getCustomer;
       $this->_paymentMethod = $PaymentMethod;
       $this->_coreRegistry = $coreRegistry;
       $this->_objectManager = $objectManager;
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

        $DistributorCollection = $this->getLocationCollection();
        $Distributors = array();
        $i = 0;
        foreach($DistributorCollection as $Distributor){

            $Distributors[$i]['id'] = $Distributor->getId();
            $Distributors[$i]['name'] = $Distributor->getName();
            $Distributors[$i]['country'] = $Distributor->getCountry();
            $Distributors[$i]['city'] = $Distributor->getCity();
            $Distributors[$i]['zip'] = $Distributor->getZip();
            $Distributors[$i]['address'] = $Distributor->getAddress();
            $Distributors[$i]['status'] = $Distributor->getStatus();
            $Distributors[$i]['lat'] = $Distributor->getLat();
            $Distributors[$i]['lng'] = $Distributor->getLng();
            $Distributors[$i]['photo'] = $Distributor->getPhoto();
            $Distributors[$i]['marker'] = $Distributor->getMarker();
            $Distributors[$i]['position'] = $Distributor->getPosition();
            $Distributors[$i]['state'] = $Distributor->getState();
            $Distributors[$i]['description'] = $Distributor->getDescription();
            $Distributors[$i]['phone'] = $Distributor->getPhone();
            $Distributors[$i]['email'] = $Distributor->getEmail();
            $Distributors[$i]['website'] = $Distributor->getWebsite();
            $Distributors[$i]['category'] = $Distributor->getCategory();
            $Distributors[$i]['store_img'] = $Distributor->getStoreImg();
            $Distributors[$i]['stores'] = $Distributor->getStores();
            $Distributors[$i]['schedule'] = $Distributor->getSchedule();
            $Distributors[$i]['marker_img'] = $Distributor->getMarkerImg();
            $Distributors[$i]['fax'] = $Distributor->getFax();
            $Distributors[$i]['show_schedule'] = $Distributor->getShowSchedule();

            $i++;
        }

        return $Distributors;
    }

    public function getLocationCollection()
    {
        if (!$this->_coreRegistry->registry('amlocator_location')) {
            /**
             * \Amasty\Storelocator\Model\Location $locationCollection
             */
            $locationCollection = $this->_objectManager->get('Amasty\Storelocator\Model\Location')->getCollection();
            $locationCollection->applyDefaultFilters();
            $locationCollection->load();
            $this->_coreRegistry->register('amlocator_location', $locationCollection);
        }

        return $this->_coreRegistry->registry('amlocator_location');
    }

}