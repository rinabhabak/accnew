<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\ConfiguratorGraphQl\Model\Resolver;

//resolver section
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Int\Configurator\Model\ResourceModel\BdmManagers\CollectionFactory as BdmManagerCollection;
use Int\Configurator\Model\Status as ConfiguratorStatus;
/**
 * Class GetCustomerConsultativeData
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class GetCustomerConsultativeData implements ResolverInterface
{

	/**
     * @var BdmManagerCollection
     */
    protected $_bdmManagerCollection;
	protected $_customer;
	protected $_configuratorFactory;
    protected $_fixtureFactory;
    protected $_openingTypesFactory;
    protected $_openingTypesCollectionFactory;
	/**
     * @var BdmManagersFactory
     */
    protected $_bdmManagersFactory;
    public function __construct(
        BdmManagerCollection $bdmManagerCollection,
        ConfiguratorStatus $configuratorStatus,
		\Magento\Customer\Model\Customer $customers,
		\Int\Configurator\Model\ConfiguratorFactory $configuratorFactory,
        \Int\Configurator\Model\FixtureFactory $fixtureFactory,
        \Int\Configurator\Model\OpeningTypesFactory $openingTypesFactory,
        \Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory $openingTypesCollectionFactory
    ) {
        $this->_bdmManagerCollection = $bdmManagerCollection;
        $this->_configuratorStatus = $configuratorStatus;
		$this->_customer = $customers;
		$this->_configuratorFactory  = $configuratorFactory;
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_openingTypesFactory  = $openingTypesFactory;
        $this->_openingTypesCollectionFactory = $openingTypesCollectionFactory;
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
		
	
		if(!isset($args['customer_id']) || $args['customer_id'] == '') {
			throw new GraphQlAuthorizationException(
                __('Your session has been expired. Please log in again.')
            );
		}
		$customerId = $args['customer_id'];
		
		$collection = $this->_configuratorFactory->create()->getCollection()->addFieldToFilter('customer_id', $customerId)->addFieldToFilter('is_consulatative_sale', 1);
		
        
		
		$output = array();
        $data = $collection->getAllIds();
		
		foreach($collection->getData() as $key=>$configurator) {
				$configuratorData = array();
			  
				$configuratorData['configurator_id'] = $configurator['configurator_id'];
				$configuratorData['customer_id'] = $configurator['customer_id'];
				$configuratorData['status'] = $configurator['status'];
				$configuratorData['type_of_build'] = $configurator['type_of_build'];
				$configuratorData['numbers_of_fixture'] = $configurator['numbers_of_fixture'];
				$configuratorData['same_fixture_dimensions'] = $configurator['same_fixture_dimensions'];
				$configuratorData['completed_at'] = $configurator['completed_at'];
				$configuratorData['config_created_at'] = $configurator['created_at'];
				$configuratorData['config_updated_at'] = $configurator['updated_at'];
				
				$configuratorId = $configurator['configurator_id'];
				 
				
				$configuratorData['fixtures'] = array();
				$fixtures = $this->_fixtureFactory->create()->getCollection();
				$fixtures->addFieldToFilter('configurator_id',$configuratorId);
				//$output['fixtures'] = $fixtures->getData();
				foreach($fixtures as $fixture){
					$_fixtureId = $fixture->getId();
					$configuratorData['fixtures'][$_fixtureId] = $fixture->getData();
					
					$_openingTypes = $this->_openingTypesCollectionFactory->create()
									->addFieldToFilter('fixture_id',$_fixtureId);
			
					if(count($_openingTypes->getAllIds())){
		
						foreach ($_openingTypes as $key => $_openingType) { 
							$_openingTypeItem = array();
							$_openingTypeItem['opening_type_id'] = $_openingType->getId();
							$_openingTypeItem['attribute_option_id'] = $_openingType->getAttributeOptionId();
							$_openingTypeItem['fixture_id'] = $_openingType->getFixtureId();
							$_openingTypeItem['name'] = $_openingType->getName();
							$_openingTypeItem['product_data'] = $_openingType->getProductData();
							$_openingTypeItem['same_as'] = $_openingType->getProductData();
							$_openingTypeItem['created_at'] = $_openingType->getCreatedAt();
							$_openingTypeItem['updated_at'] = $_openingType->getUpdatedAt();
							//$_openingTypesData['opening_types'][] = $_openingTypeItem;
							$configuratorData['fixtures'][$_fixtureId]['opening_types'][$_openingType->getId()] = $_openingTypeItem;
						}
						
					   
						
					}
						
						
				}
				
		
				$output[] = $configuratorData;
		}
		
		
		
		return $output;	
    }
	
	
	
	public function getCustomerDetails($customerId) {
        $customer = $this->_customer->load($customerId);
		if ($customer->getId()) {
            return $customer->getFirstname() .' '. $customer->getLastname();
        }
		else {
			return "";
		}
    }
}