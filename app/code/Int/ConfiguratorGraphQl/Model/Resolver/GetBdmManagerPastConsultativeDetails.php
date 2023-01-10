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
 * Class GetBdmManagerPastConsultativeDetails
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class GetBdmManagerPastConsultativeDetails implements ResolverInterface
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
		$status = isset($args['input']['status'])?$args['input']['status']:'';
		
		$collection = $this->_bdmManagerCollection->create();
		$past_date = date('Y-m-d', strtotime('-15 days'));
		$past_date = $past_date. ' 23:59:59';
		if($status == "") {
			$collection->getSelect()->join(
				['config' => $collection->getTable('configurator')],
				'config.configurator_id = main_table.parent_id',
				['config.*','config_created_at'=>'config.created_at','config_updated_at'=>'config.updated_at']			
			)->where(
				"is_consulatative_sale=1"
			);
		}
		else{
			$collection->getSelect()->join(
				['config' => $collection->getTable('configurator')],
				'config.configurator_id = main_table.parent_id',
				['config.*','config_created_at'=>'config.created_at','config_updated_at'=>'config.updated_at']
			)->where(
				"config.status = ".$status." AND is_consulatative_sale=1"
			);
		}
		
		$output = array();
        $data = $collection->getAllIds();
		
		foreach($collection->getData() as $key=> $customer) {
				$customerData = array();
			    $customerData['entity_id'] = $customer['entity_id'];
				$customerData['parent_id'] = $customer['parent_id'];
				$customerData['assigned_to'] = $this->getCustomerDetails($customer['assigned_to']);
				$customerData['created_at'] = $customer['created_at'];
				$customerData['updated_at'] = $customer['updated_at'];
				$customerData['assigned_by'] = $this->getCustomerDetails($customer['assigned_by']);
				$customerData['configurator_id'] = $customer['configurator_id'];
				$customerData['project_id'] = $customer['project_id'];
				$customerData['project_name'] = $customer['project_name'];
				$customerData['customer_id'] = $customer['customer_id'];
				$customerData['customer_name'] = $this->getCustomerDetails($customer['customer_id']);
				$customerData['status'] = $this->_configuratorStatus->getOptionText($customer['status']);
                $customerData['bds_status'] = $customer['bds_status'];
				$customerData['type_of_build'] = $customer['type_of_build'];
				$customerData['numbers_of_fixture'] = $customer['numbers_of_fixture'];
				$customerData['same_fixture_dimensions'] = $customer['same_fixture_dimensions'];
				$customerData['completed_at'] = $customer['completed_at'];
				$customerData['config_created_at'] = $customer['config_created_at'];
				$customerData['config_updated_at'] = $customer['config_updated_at'];
		
				$output[] = $customerData;
				
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