<?php
namespace Int\Configurator\Block\Adminhtml;
use Magento\Framework\Pricing\Helper\Data;
class Bom extends \Magento\Backend\Block\Widget\Container
{
	protected $_configuratorFactory;
    protected $_fixtureFactory;
    protected $_fixture;
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
        \Int\Configurator\Model\FixtureFactory $fixture,
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
        array $data = []
    )
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
        $this->_fixture = $fixture;
        parent::__construct($context, $data);
    }
	
    
	public function getFixtureDetails($fixtureId) {		
		$fixture = $this->_fixture->create()->load($fixtureId);      
		return $fixture;
	}

}
