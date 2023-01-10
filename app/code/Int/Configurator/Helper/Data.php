<?php

/**
 * Configurator data helper
 */
namespace Int\Configurator\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */    
    protected $_scopeConfig;
    
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
	protected $_configuratorFactory;
    protected $_fixtureFactory;
    protected $_openingTypesFactory;
    protected $_openingTypesCollectionFactory;
	protected $_productCollectionFactory;
	protected $_productFactory;
	protected $_priceHelper;
    private $_products;
	protected $_configuratorProductHelper;
	
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
		\Int\Configurator\Model\ConfiguratorFactory $configuratorFactory,
		\Int\Configurator\Model\ResourceModel\Fixture\CollectionFactory $fixtureFactory,
		\Int\Configurator\Model\OpeningTypesFactory $openingTypesFactory,
		\Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory $openingTypesCollectionFactory,
        \Int\Configurator\Helper\Products $configuratorProductHelper,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
		$this->_configuratorFactory = $configuratorFactory;
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_openingTypesFactory  = $openingTypesFactory;
        $this->_openingTypesCollectionFactory = $openingTypesCollectionFactory;
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->_productFactory = $productFactory;
		$this->priceHelper = $priceHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_products = [];
        $this->_configuratorProductHelper = $configuratorProductHelper;
        parent::__construct($context);
    }
    
    
   	public function getproductList($configuratorId) {
		
        $configurator = $this->_configuratorFactory->create()->load($configuratorId);
        if(!$configurator->getId()){
            throw new \Exception('Invalid configurator.');
        }
        
		$fixtureDetails = $this->getFixtureDetails($configuratorId);
		
		$fixtureCollaction = array();
		//$_products = array();
		foreach($fixtureDetails as $key=>$fixturecol) {			
			$fixtureCollaction[$fixturecol['fixture_id']] = $fixturecol['fixture_name'];			
		}
        
		$this->_products = $this->_configuratorProductHelper->getProductLists($configuratorId);
        
		$productreturns = array();
		if(count($this->_products) > 0) {
			foreach ($this->_products as $fixtureId => $products) {
				$fixtureName = '';
				if(isset($fixtureCollaction[$fixtureId])) {
					$fixtureName = $fixtureCollaction[$fixtureId];
				}
                
                $fixtureSubtotal = $this->getFixtureTotalPrice($products);
                
                
				
				// fetch logo for fixture based on fixture_id
				$logoUrl = '';
				$selectedSystem = $this->_configuratorProductHelper->getSelectedSystemsByFixtureId($fixtureId);
				if($selectedSystem !='') {
					$logoUrl = $this->_configuratorProductHelper->getSystemLogoUrl($selectedSystem);
				}
                
                $isSenseonPlus = $selectedSystem=='senseon-plus'?1:0;
				
				$productreturns['fixtures'][$fixtureId] = array(
                            'fixture_id'=>$fixtureId,
                            'fixture_logo'=>$logoUrl,
                            'fixture_name'=>$fixtureName,
                            'fixture_total' => $this->_configuratorProductHelper->getCurrencyFormat($fixtureSubtotal),
                            'is_senseon_plus' => $isSenseonPlus,
                            'products'=>$products);			
			}		
		}
		return $productreturns;
	}
	public function getFixtureDetails($configurator_id) {
				
		$fixture = $this->_fixtureFactory->create()->addFieldToFilter('configurator_id',$configurator_id);
	
		return $fixture;
	}

	public function getOptionsDetails($fixture_id) {
		
		$fixture = $this->_openingTypesCollectionFactory->create()->addFieldToFilter('fixture_id',$fixture_id);
		return $fixture;
	}
	
    
    /*
     * Get fixture total price
    */
    
    public function getFixtureTotalPrice($products){
        $total = 0;
        if(is_array($products) && !empty($products)){            
            foreach($products as $product){
                $total = $total + $product['row_total'];
            }
        }
        return $total;
    }
	
    
}
