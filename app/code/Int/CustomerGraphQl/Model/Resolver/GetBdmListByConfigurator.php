<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_CompanyGraphQl
 * @author    Indusnet
 */

namespace Int\CustomerGraphQl\Model\Resolver;


use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Int\Configurator\Model\ResourceModel\BdmManagers\CollectionFactory as BdmManagerCollectionFactory;
use Int\Configurator\Model\ResourceModel\BdmManagers\Collection as BdmManagerCollection;
use Int\Configurator\Model\Status as ConfiguratorStatus;
use Int\Configurator\Model\ConfiguratorFactory as ConfiguratorModel;
use Magento\Customer\Model\CustomerFactory as CustomerModel;


class GetBdmListByConfigurator implements ResolverInterface
{
    /**
     * @var BdmManagerCollectionFactory
     */
    protected $_bdmManagerCollectionFactory;

    /**
     * @var BdmManagerCollection
     */
    protected $_bdmManagerCollection;

    /**
     * @var ConfiguratorStatus
     */
    protected $_configuratorStatus;

    /**
     * @var ConfiguratorModel
     */
    protected $_configurator;

    /**
     * @var CustomerModel
     */
    protected $_customer;
    
    /**
     * BdmManagement Helper
     *
     * @var \Int\BdmManagement\Helper\Data
     */
    protected $_bdmHelper;
    

    /**
     * @param BdmManagerCollectionFactory $BdmManagerCollectionFactory
     * @param BdmManagerCollection $BdmManagerCollection
     * @param ConfiguratorModel $configuratorFactory
     * @param ConfiguratorStatus $configuratorStatus
     * @param CustomerModel $customerModel
     */
    public function __construct(
        BdmManagerCollectionFactory $BdmManagerCollectionFactory,
        BdmManagerCollection $bdmManagerCollection,
        ConfiguratorModel $configuratorFactory,
        ConfiguratorStatus $configuratorStatus,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers,
        \Int\BdmManagement\Helper\Data $bdmHelper
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->_customer = $customers;
        $this->groupRepository = $groupRepository;
        $this->_bdmManagerCollectionFactory = $BdmManagerCollectionFactory;
        $this->_bdmManagerCollection = $bdmManagerCollection;
        $this->_configurator = $configuratorFactory;
        $this->_configuratorStatus = $configuratorStatus;
        $this->_bdmHelper = $bdmHelper;
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
        if (!isset($args['input']['configurator_id']) || empty($args['input']['configurator_id'])) {
            throw new GraphQlInputException(__('"configurator_id" value should be specified'));
        }
        $configuratorId = $args['input']['configurator_id'];
        $bdmDatas = array();
        $bdsGroup = $this->_bdmHelper->getBdsCustomerGroup();
        $customerCollection =  $this->_customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter("group_id", array("eq" => $bdsGroup));

        foreach ($customerCollection as $customer) {
            $bdmDatas[$customer->getId()] = $customer->getData();
            $bdmDatas[$customer->getId()]['user_id'] = $customer->getId();
            $bdmDatas[$customer->getId()]['customerGroup'] = $this->getGroupName($customer->getGroupId());
            $bdmDatas[$customer->getId()]['active_task'] = $this->getActiveTasks($customer->getId());
            $bdmDatas[$customer->getId()]['is_assigned'] = $this->getBdmAssignDetails($customer->getId(),$configuratorId);

        }
        return $bdmDatas;
    }

    public function getCustomerCollection() {
        return $this->_customer->getCollection()
               ->addAttributeToSelect("*")
               ->load();
    }

    public function getFilteredCustomerCollection() {
        $bdsGroup = $this->_bdmHelper->getBdsCustomerGroup();
        return $this->_customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter("group_id", array("eq" => $bdsGroup));
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

    public function getActiveTasks($bdmId){

        if(empty($bdmId)){
            throw new \Exception('BDM Id is required.');
        }

        $collection = $this->_bdmManagerCollectionFactory->create();

        $collection->getSelect()->join(
            ['config' => $collection->getTable('configurator')],
            'config.configurator_id = main_table.parent_id',
            []
        )->where(
            "config.status=".\Int\Configurator\Model\Status::STATUS_INPROCESS." AND main_table.assigned_to=".$bdmId
        );

        $data = $collection->getAllIds();
        return count($data);
    }


    

    public function getBdmAssignDetails($customerId,$configuratorId){
        $collection = $this->_bdmManagerCollectionFactory->create()->addAssignToFilter($customerId)->addConfiguratorFilter($configuratorId);
        $data = $collection->getAllIds();
        $collection->getSelect()->reset('where');
        if(count($data) < 1){
            return false;
        }else{
            return true;
        }
    }
}