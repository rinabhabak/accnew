<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */
namespace Int\ConfiguratorGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

class ConfiguratorOrder
{
    private $_configuratorDataProvider;
    protected $_configuratorFactory;
    protected $_fixtureFactory;
    protected $_openingTypesFactory;
    protected $_openingTypesCollectionFactory;
    protected $_customerCollectionFactory;
    protected $_customerFactory;
    protected $_bdmManager;
    
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


    public function getConfigurator($projectId) {
        $output = array();
        $configurator = $this->_configuratorFactory->create()->load($projectId, 'project_id');
        if(!$configurator->getId()) {
            throw new \Exception('Invalid configurator.');
        }

        $configuratorId = $configurator->getId();

        $customer = $this->_customerFactory->load($configurator->getCustomerId());
        $customerName = $customer->getName();
        $output = $configurator->getData();
        $customerCollection = $this->getCustomers();
        $output['customer_name'] = $customerName;
        $output['customer_email'] = $customer->getEmail();

        if($configurator->getId()) {
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
        }

        return $output;
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
