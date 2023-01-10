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
 * Class GetSaveforlaterBdmManager
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class GetSaveforlaterBdmManager implements ResolverInterface
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
		
		$bds_status = isset($args['input']['bds_status'])?$args['input']['bds_status']:'';

		/*
		$collection = $this->_bdmManagerCollection->create();
			
         $collection->getSelect()->join(
            ['config' => $collection->getTable('configurator')],
            'config.configurator_id = main_table.parent_id',
            ['config.*','config_created_at'=>'config.created_at','config_updated_at'=>'config.updated_at','assigned_date'=>'main_table.created_at']
        )->where(
            "config.status IN (1,2) AND config.is_consulatative_sale = 0 AND config.bds_status IN (".$bds_status.")"
        );
		
		*/
		
		
		if($bds_status == "") {
			$collection = $this->_configuratorFactory->create()->getCollection()->addFieldToFilter('status',array('in'=>array(1,2)));
			$collection->getSelect()->joinLeft(
				['bdm' => $collection->getTable('configurator_assigned_bdm_managers')],
				'bdm.parent_id = main_table.configurator_id',
				['bdm.*', 'bdm_created_at'=>'bdm.created_at', 'config_created_at'=>'main_table.created_at', 'config_updated_at'=>'main_table.updated_at']		
			);
		}
		else{
			$bds_status = explode(',',$bds_status);
			 $collection = $this->_configuratorFactory->create()->getCollection()->addFieldToFilter('bds_status',array('in'=>$bds_status))->addFieldToFilter('status',array('in'=>array(1,2)));
			 $collection->getSelect()->joinLeft(
				['bdm' => $collection->getTable('configurator_assigned_bdm_managers')],
				'bdm.parent_id = main_table.configurator_id',
				['bdm.*', 'bdm_created_at'=>'bdm.created_at', 'config_created_at'=>'main_table.created_at', 'config_updated_at'=>'main_table.updated_at']
				
			);
				
			
		}
		
		
		$output = array();
        $data = $collection->getAllIds();
		
		foreach($collection->getData() as $key=> $customer) {
				$customerData = array();
				$customerData['assigned_to'] = !empty($customer['assigned_to'])?$this->getCustomerDetails($customer['assigned_to']):NULL;
				$customerData['assigned_date'] = $customer['bdm_created_at'];
				$customerData['bdm_id'] = $customer['assigned_to'];
				$customerData['bds_status'] = $customer['bds_status'];	
				$customerData['configurator_id'] = $customer['configurator_id'];
				$customerData['project_id'] = $customer['project_id'];
				$customerData['project_name'] = $customer['project_name'];
				$customerData['customer_id'] = $customer['customer_id'];
				$customerData['customer_name'] = $this->getCustomerDetails($customer['customer_id']);
				$customerData['status'] = $this->_configuratorStatus->getOptionText($customer['status']);
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
			return "NA";
		}
    }
}