<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\ConfiguratorGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Configurator
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class Thankyou implements ResolverInterface
{

    private $_configuratorDataProvider;
    protected $_configuratorFactory;
    protected $_fixtureFactory;
    protected $_openingTypesFactory;
    protected $_openingTypesCollectionFactory;
	protected $_customerCollectionFactory;
	protected $_customerFactory;
	protected $_bdmManager;
	
    /**
     * @param Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Configurator $configuratorDataProvider
     */
    public function __construct(
        \Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Configurator $configuratorDataProvider,
        \Int\Configurator\Model\ConfiguratorFactory $configuratorFactory,
        \Int\Configurator\Model\FixtureFactory $fixtureFactory,
        \Int\Configurator\Model\OpeningTypesFactory $openingTypesFactory,
        \Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory $openingTypesCollectionFactory,
		\Int\Configurator\Model\ResourceModel\BdmManagers\CollectionFactory $bdmManager,
		\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
		\Magento\Customer\Model\Customer $customerFactory
    ) {
        $this->_configuratorFactory  = $configuratorFactory;
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_openingTypesFactory  = $openingTypesFactory;
        $this->_openingTypesCollectionFactory = $openingTypesCollectionFactory;
		$this->_bdmManager = $bdmManager;
		$this->_customerCollectionFactory = $customerCollectionFactory;
		$this->_customerFactory = $customerFactory;
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
        
        //if (false === $context->getExtensionAttributes()->getIsCustomer()) {
        //    throw new GraphQlAuthorizationException(
        //        __('The current customer isn\'t authorized.')
        //    );
        //}
        $_output = array();
        $output = array();
        $configuratorId = $args['input']['configurator_id'];
        $configurator = $this->_configuratorFactory->create()->load($configuratorId);
        if(!$configurator->getId()){
            throw new \Exception('Invalid configurator.');
        }
        try{
            $configurator->setStatus(\Int\Configurator\Model\Status::STATUS_COMPLETE)->save();
        }catch(\Exception $e){
            
        }
        
        $customer = $this->_customerFactory->load($configurator->getCustomerId());
        $customerName = $customer->getName();
        $output = $configurator->getData();
        $customerCollection = $this->getCustomers();
		$output['customer_name'] = $customerName;
		$output['customer_email'] = $customer->getEmail();
		$output['completed_at'] = $configurator->getCompleteAt()==NULL?'':$configurator->getCompleteAt();
        
		if($configurator->getId()){
			
			$output['assigned_bdm_name'] = '';
			$output['assigned_bdm_date'] = '';
			$output['assigned_bdm_id'] = '';
			
			$bdmManager = $this->_bdmManager->create()->addFieldToFilter('parent_id',$configuratorId)->getData();
			if(count($bdmManager) > 0) {
				foreach($bdmManager as $assignData) {
					$customerDetails = $this->_customerFactory->load($assignData['assigned_to']);
					$output['assigned_bdm_name'] = $customerDetails->getName();
					$output['assigned_bdm_date'] = $assignData['updated_at'];
					$output['assigned_bdm_id'] = $assignData['entity_id'];
				}
			}
			
            $output['fixtures'] = array();
            $fixtures = $this->_fixtureFactory->create()->getCollection();
            $fixtures->addFieldToFilter('configurator_id',$configuratorId);
            $output['fixtures'] = $fixtures->getData();
            foreach($fixtures as $fixture){
                $_fixtureId = $fixture->getId();
                $output['fixtures'][$_fixtureId] = $fixture->getData();
                
                $_openingTypes = $this->_openingTypesCollectionFactory->create()
                                ->addFieldToFilter('fixture_id',$_fixtureId);
        
                if(count($_openingTypes->getAllIds())){
    
                    foreach ($_openingTypes as $key => $_openingType) { 
                        $_openingTypeItem = array();
						$productDataCollection = array();
                        $_openingTypeItem['opening_type_id'] = $_openingType->getId();
                        $_openingTypeItem['attribute_option_id'] = $_openingType->getAttributeOptionId();
                        $_openingTypeItem['fixture_id'] = $_openingType->getFixtureId();
                        $_openingTypeItem['name'] = $_openingType->getName();
                        //$_openingTypeItem['product_data'] = $_openingType->getProductData();
                        $_openingTypeItem['same_as'] = $_openingType->getProductData();
                        $_openingTypeItem['created_at'] = $_openingType->getCreatedAt();
                        $_openingTypeItem['updated_at'] = $_openingType->getUpdatedAt();
                        //$_openingTypesData['opening_types'][] = $_openingTypeItem;
		
						$serializeProductData  = unserialize($_openingType->getProductData());					

						$attributes_fields_data = $serializeProductData['attributes_fields_data'];
						
						$productDataCollection = array();
						
						if($attributes_fields_data!='') {
							foreach($attributes_fields_data as $key => $value) {
								$attributes_fields_data[$key]['option_type_id'] = $_openingType->getId();							
							}
						}
						
						
						$custom_fields_data = $serializeProductData['custom_fields_data'];					
						if($attributes_fields_data!='') {
							$output['fixtures'][$_fixtureId]['opening_types'][$_openingType->getId()]['attributes_field'] = $attributes_fields_data;
						}
						if($custom_fields_data!='') {
							$output['fixtures'][$_fixtureId]['opening_types'][$_openingType->getId()]['custom_field'] = $custom_fields_data;
						}
						
                        
                    }                 
						
                }     
                
            }
        }
        
        $_output['configurator'] = $output;
        
        return $_output;
    }
	
	
	public function getCustomers() {
		$customerData = array();
		$customerCollection = $this->_customerCollectionFactory->create()->addFieldToFilter('group_id',6);
		foreach($customerCollection->getData() as $customerData) {
			$customerData[$customerData['entity_id']] = $customerData['firstname'] .' '. $customerData['lastname'];
		}
		return $customerData;
	}

}