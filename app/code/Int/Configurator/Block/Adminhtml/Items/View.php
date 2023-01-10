<?php
namespace Int\Configurator\Block\Adminhtml\Items;
use Magento\Framework\Pricing\Helper\Data;
class View extends \Magento\Backend\Block\Widget\Container
{
	protected $_configuratorFactory;
    protected $_fixtureFactory;
    protected $_openingTypesFactory;
    protected $_openingTypesCollectionFactory;
    protected $optionFactory;
	protected $_customerRepositoryInterface;
	protected $_status;
	protected $_productCollectionFactory;
	protected $_productObj;
	protected $_imgObj;
	protected $priceHelper;
	protected $eavConfig;
	/**
     * @var BdmManagersFactory
     */
    protected $_bdmManagersFactory;
	
    public function __construct(\Magento\Backend\Block\Widget\Context $context,
								\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
								\Int\Configurator\Model\ConfiguratorFactory $configuratorFactory,
								\Int\Configurator\Model\ResourceModel\Fixture\CollectionFactory $fixtureFactory,
								\Int\Configurator\Model\OpeningTypesFactory $openingTypesFactory,
								\Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory $openingTypesCollectionFactory,
								\Int\Configurator\Model\Status $status,
								\Int\Configurator\Model\BdmManagersFactory $bdmManagersFactory,
								\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
								\Magento\Catalog\Model\ProductFactory $productObj,
								\Magento\Catalog\Helper\Image $imgObj,
								Data $priceHelper,
								\Magento\Eav\Model\Config $eavConfig,
								array $data = [])
    {
		$this->_customerRepositoryInterface = $customerRepositoryInterface;
		$this->_configuratorFactory = $configuratorFactory;
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_openingTypesFactory  = $openingTypesFactory;
        $this->_openingTypesCollectionFactory = $openingTypesCollectionFactory;
		$this->_status = $status;
		$this->_bdmManagersFactory  = $bdmManagersFactory;
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->_productObj = $productObj;
		$this->_imgObj = $imgObj;
		$this->priceHelper = $priceHelper;
		$this->eavConfig = $eavConfig;
        parent::__construct($context, $data);
    }
	
	public function getConfiguratorDetails() {
		
		$configurator_id = $this->getRequest()->getParam('id');	
		$configurator = $this->_configuratorFactory->create()->load($configurator_id);
		
		return $configurator;
	}
	
	public function getCustomerDetails($customerId) {
			$customer = $this->_customerRepositoryInterface->getById($customerId);
			return $customer;
	}
	
	public function getStatus($status_id) {
			$status = $this->_status->getOptionText($status_id);
			return $status;
	}
	
	public function getFixtureDetails() {
		
		$configurator_id = $this->getRequest()->getParam('id');			
		$fixture = $this->_fixtureFactory->create()->addFieldToFilter('configurator_id',$configurator_id);
	
		return $fixture;
	}

	public function getOptionsDetails($fixture_id) {
		
		$fixture = $this->_openingTypesCollectionFactory->create()->addFieldToFilter('fixture_id',$fixture_id);
		return $fixture;
	}
	
	public function getProductCollection($filtervalues) {
		
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
		foreach($filtervalues as $filteroptions) {
			$collection->addAttributeToFilter($filteroptions['attribute_code'],$filteroptions['attribute_option_value']);
		}
				
        return $collection;
    }
	
	public function getProductdetails($id) {
		
        return $this->_productObj->create()->load($id);
    }
	
	public function getImageUrl($prd) {
		
		$imageUrl = $this->_imgObj->init($prd, 'product_page_image_small')
                ->setImageFile($prd->getSmallImage()) // image,small_image,thumbnail
                ->resize(380)
                ->getUrl();
		return $imageUrl;
    }
	
	public function getCurrenctFormat($price) {
		
		return $this->priceHelper->currency($price, true, false);
    }
	
	public function getBDMdetails($configuratorId) {
		
		$assignBdmModel = $this->_bdmManagersFactory->create();
        $assignBdmModel->load($configuratorId, 'parent_id');
		$result = array();
		if(count($assignBdmModel->getData()) > 0) {
			$customer = $this->getCustomerDetails($assignBdmModel->getAssignedTo());
			if($customer->getId()) {
				
				$result['name'] = $customer->getFirstname().' '. $customer->getLastname();
				$result['email'] = $customer->getEmail();
				$result['created_at'] = $assignBdmModel->getCreatedAt();
				
			}
		}
		return $result;
	}
	
	public function getAttributeValues($code) {
		$attribute = $this->eavConfig->getAttribute('catalog_product',$code);
		$options = $attribute->getSource()->getAllOptions();
		$attributeValues = array();
		foreach($options as $optionsValues) {
			$attributeValues[$optionsValues['value']] = $optionsValues['label'];
		}
		return $attributeValues;
	}
}
