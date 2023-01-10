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
 * Class GetBdmManagerActiveTaskList
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class GetBdmManagerActiveTaskList implements ResolverInterface
{

	/**
     * @var BdmManagerCollection
     */
    protected $_bdmManagerCollection;
	protected $_customer;
	/**
     * @var BdmManagersFactory
     */
    protected $_bdmManagersFactory;
    public function __construct(
        BdmManagerCollection $bdmManagerCollection,
        ConfiguratorStatus $configuratorStatus,
		\Magento\Customer\Model\Customer $customers
    ) {
        $this->_bdmManagerCollection = $bdmManagerCollection;
        $this->_configuratorStatus = $configuratorStatus;
		$this->_customer = $customers;
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
		
		if(!isset($args['bdm_id']) || $args['bdm_id'] == '') {
			throw new GraphQlAuthorizationException(
                __('bdm_id is required.')
            );
		}
		
		$results = $this->getActiveTasks($args['bdm_id']);
		return $results;	
    }
	
	 public function getActiveTasks($bdmId){

        if(empty($bdmId)){
            throw new \Exception('BDM Id is required.');
        }

        $collection = $this->_bdmManagerCollection->create();

        $collection->getSelect()->join(
            ['config' => $collection->getTable('configurator')],
            'config.configurator_id = main_table.parent_id',
            ['config.*','config_created_at'=>'config.created_at','config_updated_at'=>'config.updated_at']
        )->where(
            "config.status=".\Int\Configurator\Model\Status::STATUS_INPROCESS." AND main_table.assigned_to=".$bdmId
        );
		
		$customers = array();
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
				$customerData['status'] = $customer['status'];
				$customerData['type_of_build'] = $customer['type_of_build'];
				$customerData['numbers_of_fixture'] = $customer['numbers_of_fixture'];
				$customerData['same_fixture_dimensions'] = $customer['same_fixture_dimensions'];
				$customerData['completed_at'] = $customer['completed_at'];
				$customerData['config_created_at'] = $customer['config_created_at'];
				$customerData['config_updated_at'] = $customer['config_updated_at'];
				
				$customers[] = $customerData;
		}
	
		return $customers;
		

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