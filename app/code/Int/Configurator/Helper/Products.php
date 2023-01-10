<?php

/**
 * Configurator data helper
 */
namespace Int\Configurator\Helper;
use Int\ConfiguratorPdf\Helper\Data as ConfiguratorPdfHelper;

class Products extends \Magento\Framework\App\Helper\AbstractHelper
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
    protected $eavConfig;

    protected $layoutFactory;
    private $_configuratorDataProvider;
    protected $_configuratorFactory;
    protected $_fixtureCollectionFactory;
    protected $_openingTypesFactory;
    protected $_openingTypesCollectionFactory;
    protected $optionFactory;
    protected $_customerRepositoryInterface;
    protected $_status;
    protected $_productCollectionFactory;
    protected $_productFactory;
    protected $_imgObj;
    protected $priceHelper;
    protected $_configuratorPdfHelper;
    protected $_directory;
    protected $_fixtureFactory;
    private $_products;
    private $_fixtureId;
    private $_systems;
    protected $_productResourceModel;
    
    protected $_configuratorSubtotal;
        
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Int\Configurator\Model\ConfiguratorFactory $configuratorFactory,
        \Int\Configurator\Model\ResourceModel\Fixture\CollectionFactory $fixtureCollectionFactory,
        \Int\Configurator\Model\OpeningTypesFactory $openingTypesFactory,
        \Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory $openingTypesCollectionFactory,
        \Int\Configurator\Model\Status $status,
        \Int\Configurator\Model\FixtureFactory $fixtureFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceModel,
        \Magento\Catalog\Helper\Image $imgObj,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ConfiguratorPdfHelper $configuratorPdfHelper,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Filesystem\DirectoryList $directory
        
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_configuratorFactory = $configuratorFactory;
        $this->_fixtureCollectionFactory  = $fixtureCollectionFactory;
        $this->_openingTypesFactory  = $openingTypesFactory;
        $this->_openingTypesCollectionFactory = $openingTypesCollectionFactory;
        $this->_status = $status;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productFactory = $productFactory;
        $this->_imgObj = $imgObj;
        $this->priceHelper = $priceHelper;
        $this->_configuratorPdfHelper = $configuratorPdfHelper;
        $this->resultRawFactory      = $resultRawFactory;
        $this->fileFactory           = $fileFactory;
        $this->_directory = $directory;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_fixtureFactory = $fixtureFactory;
        $this->eavConfig = $eavConfig;
        $this->productFactory = $productFactory;
        $this->_productResourceModel = $productResourceModel;
        
        parent::__construct($context);
        $this->_products = [];
        $this->_systems = [];
        $this->_fixtureId = 0;
        $this->_configuratorSubtotal = 0;
    }
    
    
    
    public function getProductLists($configuratorId)
    {
        
        $configurator = $this->_configuratorFactory->create()->load($configuratorId);
        if(!$configurator->getId()){
            throw new \Exception('Invalid configurator.');
        }
      
        $output = array();
        $output = $configurator->getData();
       
        // Fetch customer details
        
        $customerDetails = $this->getCustomerDetails($configurator->getCustomerId());
        $fixtureDetails = $this->getFixtureDetails($configurator->getId());
        $type_of_build = $configurator->getTypeOfBuild();
        //$_products = array();
        $plus_system=0;
        foreach($fixtureDetails as $key=>$fixturecol) {
            $fixtureId = $fixturecol['fixture_id'];
            $this->_fixtureId = $fixtureId;
            $this->_products[$this->_fixtureId] = array();
            
            $currentSystem = $this->getSelectedSystemsByFixtureId($fixtureId);
            if($currentSystem=='senseon-plus'){
                $plus_system++;
            }
            
            $optionsdetails = $this->getOptionsDetails($fixturecol['fixture_id']);
            $additionalData = unserialize($fixturecol['additional_data']);
            $nop = count($optionsdetails);
            $number_of_hinged_c_door = 0;
            $sliding_cabinet_door = 0;
            $sliding_cabinet_door_others = 0;
            $sliding_cabinet_door_glass = 0;
            $number_of_standalone_lock = 0;
            $cabinet_spec_depth = $fixturecol['fixture_depth'];
            foreach($optionsdetails as $keycounter=>$_openingType) {
                if($_openingType->getProductData() !='') {
                    
                    $serializeProductData  = unserialize($_openingType->getProductData());
                    $typeId = $_openingType->getAttributeOptionId();
                    $type = $this->getAttributeOptionText('type_of_opening_drawer',$typeId);
                    $type = str_replace(' ', '_', trim(strtolower($type)));
        
                    $attributes_fields_data = $serializeProductData['attributes_fields_data'];
                    $custom_fields_data = $serializeProductData['custom_fields_data'];
                    $productCollections = $this->getProductCollection($type,$attributes_fields_data,$custom_fields_data,$additionalData,'depth');
                    
                    $drawer_opening_spec_width = 0;
                    $drawer_opening_spec_depth = 0;
                   
                    
                    if(!empty($custom_fields_data)){
                        foreach($custom_fields_data as $custom_field){
                            if(isset($custom_field['field_name']) &&  $custom_field['field_name']=='width'){
                                if($custom_field['field_value'] !='') {                   
                                    $drawer_opening_spec_width = $custom_field['field_value'];
                                }
                            }
                            
                            if(isset($custom_field['field_name']) &&  $custom_field['field_name']=='depth'){
                                if($custom_field['field_value'] !='') {                   
                                    $drawer_opening_spec_depth = $custom_field['field_value'];
                                }
                            }
                        }
                    }
                    
                    
                    if(count($productCollections) > 0 ) {
                        foreach($productCollections as $product) {  
                            $_productItem = array();
                            $_product = $this->getProductdetails($product->getId());
                            if(array_key_exists($_product->getSku(),$this->_products[$this->_fixtureId])){
                                $_productItem['qty'] = $this->_products[$this->_fixtureId][$_product->getSku()]['qty']+1;
                            }else{
                                $_productItem['qty'] = 1;
                            }
                            
                            $this->addProductsToBOMList($_product->getSku(),$_productItem['qty']);
                            
                        }
                    }
                    
                    foreach($attributes_fields_data as $attributes_field){
                        
                        //"Sliding Door Type
                        //  In the case of sliding_door_type wood, return options whose product_type is Accessories 4180-0764-XE.
                        //  In the case of sliding_door_type glass, return options whose product_type is Accessories 4180-0763-XE.
                        //  In the case of sliding_door_type other, return options whose product_type is Accessories 4180-0764-XE"
                        
                        if($attributes_field['attribute_code'] =='sliding_door_type'){
                            $sliding_door_type = $this->getAttributeOptionText('sliding_door_type',$attributes_field['attribute_option_value']);
                            $sliding_cabinet_door++;
                            if($sliding_door_type== 'Wood' || $sliding_door_type=='Other'){
                                
                                $sliding_cabinet_door_others++;
                                
                                if($this->canAddProductToBom('4180-0764-XE',$fixtureId)){
                                    $this->addProductsToBOMList('4180-0764-XE',$sliding_cabinet_door_others);
                                }
                                
                            }else{
                                
                                $sliding_cabinet_door_glass++;
                                
                                if($this->canAddProductToBom('4180-0763-XE',$fixtureId)){
                                    $this->addProductsToBOMList('4180-0763-XE',$sliding_cabinet_door_glass);
                                }
                                
                            }
                        }
                        
                        
                        //"Type of Opening
                        //In the case of drawer_locking_solution standalone lock, return results for product_type accessories 4180-0755XE
                        //Example: Customer selects standalone locking solution, one 4180-0755XE gets added to their BOM."              
                        
                        if($attributes_field['attribute_code'] =='drawer_locking_solution'){
                            
                            $drawerLockingSolutionLabel = $this->getAttributeOptionText('drawer_locking_solution',$attributes_field['attribute_option_value']);
                            $drawerLockingSolutionLabel = strtolower(trim($drawerLockingSolutionLabel));
                            if($drawerLockingSolutionLabel != 'standalone'){
                                $number_of_standalone_lock++;
                                
                                if(($drawer_opening_spec_depth >= 12.63 && $drawer_opening_spec_depth <= 30.63
                                    && $type=='drawer' && $drawerLockingSolutionLabel=='undermount') || (
                                      $drawer_opening_spec_depth >= 14.63 && $drawer_opening_spec_depth <= 32.10
                                       && $type=='drawer' && $drawerLockingSolutionLabel=='sidemount')
                                ){
                                    
                                    foreach($productCollections as $product){
                                        $sku = $product->getSku();                                       
                                        if($product->getExcludeFromUndermountAndSi()){
                                           
                                            if(isset($this->_products[$this->_fixtureId][$sku])){
                                                if($this->_products[$this->_fixtureId][$sku]['qty']>1){
                                                    $this->_products[$this->_fixtureId][$sku]['qty'] = $this->_products[$this->_fixtureId][$sku]['qty']-1;
                                                }else{
                                                    unset($this->_products[$this->_fixtureId][$sku]);
                                                }
                                            }
                                        }
                                    }
                                }                       
                            }
                        }
                        
                        
                    }
                    
                    
                    //In the case of type_of_opening is hinged_cabinet_door,
                    //add 1 type_of_product component 2614-0125-UE per number of door openings
                    /* if($typeId==17762){
                        $number_of_hinged_c_door++;
                        
                        if($this->canAddProductToBom('2614-0125-UE',$fixtureId)){
                            $this->addProductsToBOMList('2614-0125-UE',$number_of_hinged_c_door);
                        }
                    } */
                    
                    
                    
                    
                    
                    
                    $additional_datas = unserialize($fixturecol['additional_data']);
                    if(!empty($additional_datas)){
                        foreach($additional_datas as $additional_data){                                    
                            $additional_fields = isset($additional_data['attributes_fields_data'])?$additional_data['attributes_fields_data']:array();                                        
                            if(!empty($additional_data)){                                            
                                foreach($additional_fields as $additional_field){
                                    if($additional_field['attribute_code']=='additional_features'){
                                        $additional_field_values = isset($additional_field['attribute_option_value'])?$additional_field['attribute_option_value']:array();
                                        $additional_field_values = array_map('trim', explode(',', $additional_field_values));
                                        $additional_field_value = array_filter($additional_field_values);
                                        $additional_field_value = $this->getAttributeAllOptions('additional_features',$additional_field_value);

                                        if((count($additional_field_value)==0)
                                           || (count($additional_field_value)==1 && reset($additional_field_value)=='auto_relock')){
                                            
                                            if($cabinet_spec_depth>18){
                                                //In the case of type_of_opening hinged_cabinet_door
                                                //and sliding_cabinet_door with a cabinet_spec_depth is >18" and the only feature is auto-relock,
                                                //return quantity of 1 option whose product_type is components SECBLX-24-KIT1 per number of opening.
                                                
                                                if($this->canAddProductToBom('SECBLX-24-KIT1',$fixtureId)){
                                                    
                                                    $qty = isset($this->_products[$this->_fixtureId]['SECBLX-24-KIT1']['qty'])?$this->_products[$this->_fixtureId]['SECBLX-24-KIT1']['qty']:0;
                                                    $qty = $qty+1;
                                                    $this->addProductsToBOMList('SECBLX-24-KIT1',$qty);
                                                    
                                                }
                                                
                                                
                                            }
                                        
                                        }
                                        
                                        if(count($additional_field_value)==1 && reset($additional_field_value)=='auto_relock'){
                                            
                                            if($type=='drawer'){
                                                
                                                if($drawer_opening_spec_width>18 && $drawer_opening_spec_width<24){
                                                    
                                                    //In the case of drawer_opening_spec_width is >18""
                                                    //and the only feature is auto-relock,
                                                    //return quantity of 1 option whose product_type is components
                                                    //SECBLX-24-KIT1
                                                    if($this->canAddProductToBom('SECBLX-24-KIT1',$fixtureId)){
                                                        $qty = isset($this->_products[$this->_fixtureId]['SECBLX-24-KIT1']['qty'])?$this->_products[$this->_fixtureId]['SECBLX-24-KIT1']['qty']:0;
                                                        $qty = $qty+1;
                                                        $this->addProductsToBOMList('SECBLX-24-KIT1',$qty);
                                                    }
                                                    
                                                    
                                                }elseif($drawer_opening_spec_width>=24 && $drawer_opening_spec_width<=30){                                                  
                                                    
                                                    //In the case of drawer_opening_spec_width is between 24"" - 30""
                                                    //and the only feature is auto-relock,
                                                    //return quantity of 2 options whose product_type is components
                                                    //SECBLX-24-KIT1 per number of opening
                                                    
                                                    if($this->canAddProductToBom('SECBLX-24-KIT1',$fixtureId)){
                                                        $qty = isset($this->_products[$this->_fixtureId]['SECBLX-24-KIT1']['qty'])?$this->_products[$this->_fixtureId]['SECBLX-24-KIT1']['qty']:0;
                                                        $qty = $qty+2;
                                                        $this->addProductsToBOMList('SECBLX-24-KIT1',$qty);
                                                    }
                                                    
                                                }elseif($drawer_opening_spec_width>36){
                                                    
                                                    //In the case of drawer_opening_spec_width is >36""
                                                    //and the only feature is auto-relock,
                                                    //return quantity of 3 option whose product_type is components SECBLX-24-KIT1
                                                    //per number of opening
                                                    
                                                    if($this->canAddProductToBom('SECBLX-24-KIT1',$fixtureId)){
                                                        $qty = isset($this->_products[$this->_fixtureId]['SECBLX-24-KIT1']['qty'])?$this->_products[$this->_fixtureId]['SECBLX-24-KIT1']['qty']:0;
                                                        $qty = $qty+3;
                                                        $this->addProductsToBOMList('SECBLX-24-KIT1',$qty);
                                                    }
                                                    
                                                }
                                                
                                            }
                                            
                                        }else{
                                            
                                            if($type=='drawer'){
                                                
                                                
                                                $drawer_locking_solution    = '';
                                                $drawer_lock_function       = '';
                                                foreach($attributes_fields_data as $attributes_field){
                                                    
                                                    if($attributes_field['attribute_code'] =='drawer_locking_solution'){
                                                        $drawer_locking_solution = $this->getAttributeOptionText('drawer_locking_solution',$attributes_field['attribute_option_value']);
                                                    }
                                                    
                                                    if($attributes_field['attribute_code'] =='drawer_lock_function'){
                                                        $drawer_lock_function = $this->getAttributeOptionText('drawer_lock_function',$attributes_field['attribute_option_value']);
                                                    }
                                                    
                                                }
                                                    
                                                
                                                
                                                
                                                if($drawer_opening_spec_width>18 && $drawer_opening_spec_width<24){
                                                    
                                                    if($this->canAddProductToBom('SPLUS-CB-H2L24',$fixtureId)){
                                                        $qty = isset($this->_products[$this->_fixtureId]['SPLUS-CB-H2L24']['qty'])?$this->_products[$this->_fixtureId]['SPLUS-CB-H2L24']['qty']:0;
                                                        $qty = $qty+1;
                                                        $this->addProductsToBOMList('SPLUS-CB-H2L24',$qty); 
                                                    }
                                                    
                                                    
                                                }elseif($drawer_opening_spec_width>=24 && $drawer_opening_spec_width<=30){                                                  
                                                    
                                                    //In the case of drawer_opening_spec_width is between 24"" - 30""
                                                    //and the only feature is auto-relock,
                                                    //return quantity of 2 options whose product_type is components SPLUS-CB-H2L24
                                                    //per number of opening
                                                    
                                                    if($this->canAddProductToBom('SPLUS-CB-H2L24',$fixtureId)){
                                                        $qty = isset($this->_products[$this->_fixtureId]['SPLUS-CB-H2L24']['qty'])?$this->_products[$this->_fixtureId]['SPLUS-CB-H2L24']['qty']:0;
                                                        $qty = $qty+2;
                                                        $this->addProductsToBOMList('SPLUS-CB-H2L24',$qty);
                                                    }
                                                    
                                                }elseif($drawer_opening_spec_width>36){
                        
                                                    
                                                    //In the case of drawer_opening_spec_width is >36"" and
                                                    //the only feature is auto-relock,
                                                    //return quantity of 3 option whose product_type is components SPLUS -CB-H2L24 per number of opening
                                                    
                                                    if($this->canAddProductToBom('SPLUS-CB-H2L24',$fixtureId)){
                                                        $qty = isset($this->_products[$this->_fixtureId]['SPLUS-CB-H2L24']['qty'])?$this->_products[$this->_fixtureId]['SPLUS-CB-H2L24']['qty']:0;
                                                        $qty = $qty+3;
                                                        $this->addProductsToBOMList('SPLUS-CB-H2L24',$qty); 
                                                    }
                                                }
                                                
                                               
                                                if((count($additional_field_value)>=1 && in_array('integration_with_3rd_party_system',$additional_field_value))){
                                                    
                                                    //foreach($attributes_fields_data as $attributes_field){
                                                    //                                                            
                                                    //    if($attributes_field['attribute_code'] =='drawer_lock_function'){
                                                    //        $drawer_lock_function = $this->getAttributeOptionText('drawer_lock_function',$attributes_field['attribute_option_value']);
                                                            if($drawer_lock_function=='Touch Release' && $drawer_locking_solution!=='Standalone'){
                                                                if($this->canAddProductToBom('SEPWRF-KIT10',$fixtureId)){
                                                                    $qty = isset($this->_products[$this->_fixtureId]['SEPWRF-KIT10']['qty'])?$this->_products[$this->_fixtureId]['SEPWRF-KIT10']['qty']:0;
                                                                    $qty = $qty+2;
                                                                    $this->addProductsToBOMList('SEPWRF-KIT10',$qty); 
                                                                }
                                                            }else{
                                                                if($this->canAddProductToBom('SEPWRF-KIT10',$fixtureId)){
                                                                    $qty = isset($this->_products[$this->_fixtureId]['SEPWRF-KIT10']['qty'])?$this->_products[$this->_fixtureId]['SEPWRF-KIT10']['qty']:0;
                                                                    $qty = $qty+1;
                                                                    $this->addProductsToBOMList('SEPWRF-KIT10',$qty); 
                                                                }
                                                            }
                                                    //    }
                                                    //}
                                                    
                                                }else{
                                                   
                                                        
                                                    if(
                                                       ($drawer_lock_function=='Touch Release' && $drawer_locking_solution=='Sidemount')
                                                       || $drawer_locking_solution=='Undermount'
                                                    ){
                                                        
                                                        if($this->canAddProductToBom('SPLUS-HUBKIT2L',$fixtureId)){
                                                            $qty = isset($this->_products[$this->_fixtureId]['SPLUS-HUBKIT2L']['qty'])?$this->_products[$this->_fixtureId]['SPLUS-HUBKIT2L']['qty']:0;
                                                            $qty = $qty+1;
                                                            $this->addProductsToBOMList('SPLUS-HUBKIT2L',$qty);
                                                        }

                                                    }
                                                      
                                                }
                                                
                                            }
                                            
                                            // sliding_cabinet_door || hinged_cabinet_door
                                            if($type=='hinged_cabinet_door' || $type=='sliding_cabinet_door'){
                                                if($cabinet_spec_depth>18){
                                                    
                                                    if($this->canAddProductToBom('SPLUS-CB-H2L24',$fixtureId)){
                                                        
                                                        $qty = isset($this->_products[$this->_fixtureId]['SPLUS-CB-H2L24']['qty'])?$this->_products[$this->_fixtureId]['SPLUS-CB-H2L24']['qty']:0;
                                                        $qty = $qty+1;
                                                        $this->addProductsToBOMList('SPLUS-CB-H2L24',$qty);
                                                        
                                                    }
                                                    
                                                }
                                                
                                                
                                                if((count($additional_field_value)>=1 && in_array('integration_with_3rd_party_system',$additional_field_value))){
                                                    if($this->canAddProductToBom('SEPWRF-KIT10',$fixtureId)){
                                                        $qty = isset($this->_products[$this->_fixtureId]['SEPWRF-KIT10']['qty'])?$this->_products[$this->_fixtureId]['SEPWRF-KIT10']['qty']:0;
                                                        $qty = $qty+1;
                                                        $this->addProductsToBOMList('SEPWRF-KIT10',$qty); 
                                                    }     
                                                }
                                                
                                                
                                            }
                                            
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
           
            /**"Number of Fixtures
            In the case of number_of_fixtures ≥ 1, return options whose product_type is components SPLUS-US-PROXW and two SPLUS -CB-R2C24 when any combination of auto_relock, dual_authorization, alarm_and_alert, audit_trail, or discrete_access are selected.
            Example: Customer enters 5 fixtures, selects only auto-relock from special features screen, add 5 SEPROX-US-KIT1 and two SECBLX-24-KIT1 to BOM"*/
            
            $fixtureDepth = $fixturecol['fixture_depth'];
            $numbers_of_fixture = $configurator->getNumbersOfFixture();
            $total_opening_types = 0;
            $opening_types = unserialize($fixturecol['opening_types_data']);
            //$total_opening_types = count($opening_types);
            if(!empty($opening_types)){
                foreach($opening_types as $opening_type){
                    if($opening_type['quantity']!=0){
                        $total_opening_types +=$opening_type['quantity'];
                    }
                }
            }else{
                $total_opening_types = 0;
            }
            
            $additional_datas = unserialize($fixturecol['additional_data']);
            if(!empty($additional_datas)){                                    
                foreach($additional_datas as $additional_data){                                    
                    $additional_fields = isset($additional_data['attributes_fields_data'])?$additional_data['attributes_fields_data']:array();
                    if(!empty($additional_data)){                                            
                        foreach($additional_fields as $additional_field){
                            if($additional_field['attribute_code']=='additional_features'){
                                $additional_field_values = isset($additional_field['attribute_option_value'])?$additional_field['attribute_option_value']:array();
                                $additional_field_values = array_map('trim', explode(',', $additional_field_values));
                                $additional_field_value = array_filter($additional_field_values);
                                $additional_field_value = $this->getAttributeAllOptions('additional_features',$additional_field_value);
                            
                                if(count($additional_field_value)>=1){
                                    
                                    /* In the case of number_of_fixtures ≥ 1,
                                     * return options whose product_type is components
                                     * SPLUS-US-PROXW when any combination of
                                     * auto_relock, dual_authorization, alarm_and_alert, audit_trail, or discrete_access are selected
                                     * for every fixture.
                                     * 
                                    **/
                                    
                                    if($this->canAddProductToBom('SPLUS-US-PROXW',$fixtureId)){
                                        $qty = isset($this->_products[$this->_fixtureId]['SPLUS-US-PROXW']['qty'])?$this->_products[$this->_fixtureId]['SPLUS-US-PROXW']['qty']:0;
                                        $qty = $qty+1;
                                        $this->addProductsToBOMList('SPLUS-US-PROXW',$qty);
                                    }

                                    /*
                                     * If any of the following 4 features are checked (Dual Authorization, Alarm and Alert, Discreet Access, User Audit)
                                     * the configurator defaults to Senseon Plus (which is a consultative sale) and 1 each of
                                     * SPLUS –CDPRGKIT and
                                     * SPLUS-GWAY-USK
                                     * parts will be added to the BOM.
                                    */

                                    if($this->canAddProductToBom('SPLUS-CDPRGKIT',$fixtureId)){
                                        $qty = isset($this->_products[$this->_fixtureId]['SPLUS-CDPRGKIT']['qty'])?$this->_products[$this->_fixtureId]['SPLUS-CDPRGKIT']['qty']:0;
                                        $qty = $qty+1;
                                        $this->addProductsToBOMList('SPLUS-CDPRGKIT',$qty);
                                    }

                                    if($this->canAddProductToBom('SPLUS-GWAY-USK',$fixtureId)){
                                        $qty = isset($this->_products[$this->_fixtureId]['SPLUS-GWAY-USK']['qty'])?$this->_products[$this->_fixtureId]['SPLUS-GWAY-USK']['qty']:0;
                                        $qty = $qty+1;
                                        $this->addProductsToBOMList('SPLUS-GWAY-USK',$qty);
                                    }

                                    /*
                                    Drawers
                                    Hub for 1 lock
                                    In the case of number_of_openings ≥ 1, add 1 SPLUS-HUBKIT1L per opening when customer selects Locking solution Standalone lock function regular, easy close, auto open, Touch release or drawer_locking_solution Sidemount and drawer_lock_function regular, Auto Open and any combination of auto_relock, dual_authorization, alarm_and_alert, audit_trail, and/or discrete_access.

                                    Sliding Cabinet  Door
                                    Hub for 1 lock
                                    In the case of number_of_openings ≥ 1, add 1 SPLUS-HUBKIT1L per opening when customer selects type of opening Sliding Cabinet door and any combination of auto_relock, dual_authorization, alarm_and_alert, audit_trail, and/or discrete_access.

                                    Hinged Cabinet Door
                                    In the case of number_of_openings ≥ 1, add 1 SPLUS-HUBKIT1L per opening when customer selects type of opening Hinged Cabinet Door and any combination of auto_relock, dual_authorization, alarm_and_alert, audit_trail, and/or discrete_access.
                                    */

                                    $hub2Qty = isset($this->_products[$this->_fixtureId]['SPLUS-HUBKIT2L']['qty'])?$this->_products[$this->_fixtureId]['SPLUS-HUBKIT2L']['qty']:0;
                                    $hub1Qty = isset($this->_products[$this->_fixtureId]['SPLUS-HUBKIT1L']['qty'])?$this->_products[$this->_fixtureId]['SPLUS-HUBKIT1L']['qty']:0;
                                    $hub1Qty = $hub1Qty > $total_opening_types ? $hub1Qty : $total_opening_types;

                                    if($hub2Qty > 0){
                                        $hub1Qty = $hub1Qty - $hub2Qty;
                                    }
                                    if($type=='drawer'){
                                        if($drawer_locking_solution=='Sidemount' && ($drawer_lock_function=='Regular' || $drawer_lock_function=='Auto Open')){
                                            if($this->canAddProductToBom('SPLUS-HUBKIT1L',$fixtureId)){
                                                $this->addProductsToBOMList('SPLUS-HUBKIT1L',$hub1Qty);
                                            }
                                        }
                                        if($drawer_locking_solution=='Standalone'){
                                            if($this->canAddProductToBom('SPLUS-HUBKIT1L',$fixtureId)){
                                                $this->addProductsToBOMList('SPLUS-HUBKIT1L',$hub1Qty);
                                            }
                                        }
                                    }else {
                                        if($this->canAddProductToBom('SPLUS-HUBKIT1L',$fixtureId)){
                                            $this->addProductsToBOMList('SPLUS-HUBKIT1L',$hub1Qty);
                                        }
                                    }

                                    /*
                                     * If auto_relock is true and dual_authentication,
                                     * discrete_access, alarm_and_alert, lock_status_feedback, are also true,
                                     * add 1 SPLUS-PROG-KIT to the BOM per configuration.
                                    **/

                                    /**
                                     * Disable as asked on [SPB-261]
                                     */
                                   /* if(!in_array('integration_with_3rd_party_system',$additional_field_value) && $plus_system==1){
                                        
                                        if($this->canAddProductToBom('SPLUS-PROG-KIT',$fixtureId)){
                                            $qty = isset($this->_products[$this->_fixtureId]['SPLUS-PROG-KIT']['qty'])?$this->_products[$this->_fixtureId]['SPLUS-PROG-KIT']['qty']:0;
                                            $qty = 1;
                                            $this->addProductsToBOMList('SPLUS-PROG-KIT',$qty);
                                        }
                                    }*/
                                    
                                    
                                    
                                  
                                }
                                
                                
                                
                                if(count($additional_field_value)==0){
                                    //auto-relock
                                    
                                    
                                    //In the case of number_of_openings 1 - 1,000 return options whose product_type
                                    //is components SEHUBX-KIT1
                                    //and two SELINK-BTM-KIT1 if only auto relock is selected
                                    if($this->canAddProductToBom('SEHUBX-KIT1',$fixtureId)){
                                        $qty = isset($this->_products[$this->_fixtureId]['SEHUBX-KIT1']['qty'])?$this->_products[$this->_fixtureId]['SEHUBX-KIT1']['qty']:0;
                                        $qty = $qty>$total_opening_types?$qty:$total_opening_types;
                                        $this->addProductsToBOMList('SEHUBX-KIT1',$qty);
                                    }
                                    
                                    /*
                                    if($this->canAddProductToBom('SPLUS-HUBKIT1L',$fixtureId)){
                                        $qty = isset($this->_products[$this->_fixtureId]['SPLUS-HUBKIT1L']['qty'])?$this->_products[$this->_fixtureId]['SPLUS-HUBKIT1L']['qty']:0;
                                        $qty = $qty>$total_opening_types?$qty:$total_opening_types;
                                        $this->addProductsToBOMList('SPLUS-HUBKIT1L',$qty);
                                    }*/
                                    
                                    //In the case of number_of_fixtures ≥ 1,
                                    //return options whose product_type is components SEPROX-US-KIT1
                                    //when only auto-relock is selected or from the features screen
                                    //or no features ares are selected for every fixture
                                    
                                    if($this->canAddProductToBom('SEPROX-US-KIT1',$fixtureId)){
                                        $qty = isset($this->_products[$this->_fixtureId]['SEPROX-US-KIT1']['qty'])?$this->_products[$this->_fixtureId]['SEPROX-US-KIT1']['qty']:0;
                                        $qty = $qty+1;
                                        $this->addProductsToBOMList('SEPROX-US-KIT1',$qty);
                                    }
                                }
                                
                                
                                if(count($additional_field_value)==1 && reset($additional_field_value)=='auto_relock'){
                                    //In the case of number_of_fixtures ≥ 1,
                                    //return options whose product_type is components SEPROX-US-KIT1
                                    //when only auto-relock is selected or from the features screen
                                    //or no features ares are selected for every fixture
                                    
                                    if($this->canAddProductToBom('SEPROX-US-KIT1',$fixtureId)){
                                        $qty = isset($this->_products[$this->_fixtureId]['SEPROX-US-KIT1']['qty'])?$this->_products[$this->_fixtureId]['SEPROX-US-KIT1']['qty']:0;
                                        $qty = $qty+1;
                                        $this->addProductsToBOMList('SEPROX-US-KIT1',$qty);
                                    }
                                }
                                
                                
                                // Number of Openings                               
                                if($total_opening_types>=1 && $total_opening_types<=1000){
                                    
                                   
                                    
                                    if(count($additional_field_value)==1 && reset($additional_field_value)=='auto_relock'){
                                        //auto-relock
                                        
                                        //In the case of number_of_openings 1 - 1,000 return options whose product_type
                                        //is components SEHUBX-KIT1
                                        //and two SELINK-BTM-KIT1 if only auto relock is selected
                                        if($this->canAddProductToBom('SEHUBX-KIT1',$fixtureId)){
                                            $qty = isset($this->_products[$this->_fixtureId]['SEHUBX-KIT1']['qty'])?$this->_products[$this->_fixtureId]['SEHUBX-KIT1']['qty']:0;
                                            $qty = $qty>$total_opening_types?$qty:$total_opening_types;
                                            $this->addProductsToBOMList('SEHUBX-KIT1',$qty);
                                            
                                        }
                                        
                                    }else{
                                        
                                        if($type=='sliding_cabinet_door'){
                                            if($this->canAddProductToBom('SEHUBX-KIT1',$fixtureId)){
                                                $qty = isset($this->_products[$this->_fixtureId]['SEHUBX-KIT1']['qty'])?$this->_products[$this->_fixtureId]['SEHUBX-KIT1']['qty']:0;
                                                $qty = $qty+$sliding_cabinet_door;
                                                $this->addProductsToBOMList('SEHUBX-KIT1',$qty);
                                            }
                                            
                                            if($this->canAddProductToBom('SEPROX-US-KIT1',$fixtureId)){
                                                $qty = isset($this->_products[$this->_fixtureId]['SEPROX-US-KIT1']['qty'])?$this->_products[$this->_fixtureId]['SEPROX-US-KIT1']['qty']:0;
                                                $qty = $qty+1;
                                                $this->addProductsToBOMList('SEPROX-US-KIT1',$qty);
                                            }
                                            
                                        }
                                        
                                        
                                    }
                                    
                                    
                                }
                                
                            }
                        }
                    }
                }
            }
            
        }
        
        return $this->_products;
        
    }
    
    public function getCustomerDetails($customerId) {
            $customer = $this->_customerRepositoryInterface->getById($customerId);
            return $customer;
    }
    
    public function getStatus($status_id) {
            $status = $this->_status->getOptionText($status_id);
            return $status;
    }
    
    public function getFixtureDetails($configurator_id) {
                
        $fixture = $this->_fixtureCollectionFactory->create()->addFieldToFilter('configurator_id',$configurator_id);
    
        return $fixture;
    }

    public function getOptionsDetails($fixture_id) {
        
        $fixture = $this->_openingTypesCollectionFactory->create()->addFieldToFilter('fixture_id',$fixture_id);
        return $fixture;
    }
    
    public function getProductCollection($type,$filtervalues,$custom_fields,$additional_datas,$customFiledName='width') {
        
        
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('status',array('eq'=>1));
        $collection->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $collection->addAttributeToSelect('*');
        foreach($filtervalues as $filteroptions) {
            if($filteroptions['attribute_code']=='drawer_opening_spec_sidespace'){
                continue;
            }
            
            if($filteroptions['attribute_code']=='drawer_locking_solution'
               || $filteroptions['attribute_code']=='hinged_door_lock_function'
               || $filteroptions['attribute_code']=='sliding_door_type'
            ){
                $collection->addAttributeToFilter($filteroptions['attribute_code'], array('finset' => array($filteroptions['attribute_option_value'])));
            }else{
                $collection->addAttributeToFilter($filteroptions['attribute_code'],$filteroptions['attribute_option_value']);
            }
            
        }
        
        if($type=='drawer'){
            
            if(!empty($custom_fields)){
                foreach($custom_fields as $custom_field){
                    if($customFiledName=='width'){
                        if(isset($custom_field['field_name']) &&  $custom_field['field_name']=='width' ){
                            if($custom_field['field_value'] !='') {                   
                                $collection->addAttributeToFilter('opening_spec_length_drawer_box_min', array('lteq' => (float)$custom_field['field_value']));
                                $collection->addAttributeToFilter('opening_spec_length_drawer_box_max', array('gteq' => (float)$custom_field['field_value']));
                            }
                        }
                    }
                    
                    if($customFiledName=='depth'){
                        if(isset($custom_field['field_name']) &&  $custom_field['field_name']=='depth'){
                            if($custom_field['field_value'] !='') {                   
                                $collection->addAttributeToFilter('opening_spec_length_drawer_box_min', array('lteq' => (float)$custom_field['field_value']));
                                $collection->addAttributeToFilter('opening_spec_length_drawer_box_max', array('gteq' => (float)$custom_field['field_value']));
                            }
                        }
                    }
                }
            }
            
        
        }
        
        
        if(!empty($additional_datas)){
           
            foreach($additional_datas as $additional_data){
                $additional_fields = isset($additional_data['attributes_fields_data'])?$additional_data['attributes_fields_data']:array();
                
                if(!empty($additional_data)){
                    
                    foreach($additional_fields as $additional_field){
                        
                        $additional_field_values = isset($additional_field['attribute_option_value'])?$additional_field['attribute_option_value']:array();
                        $additional_field_values = array_map('trim', explode(',', $additional_field_values));
                        $additional_field_value = array_filter($additional_field_values);
                        $additional_field_value = $this->getAttributeAllOptions('additional_features',$additional_field_value);
                       
                      
                        if(count($additional_field_value)==0){
                            //If auto_relock is not true and dual_authentication, discrete_access,
                            //alarm_and_alert, lock_status_feedback,
                            //and intergration_3rd_party are also not true then system is Senseon One. 
                            $senseonOne = $this->getAttributeOptionId('system_compatibility','Senseon One'); 
                            $collection->addAttributeToFilter('system_compatibility',array('finset' => array($senseonOne)));                          
                        }
                        
                        
                        if(count($additional_field_value)==1  && in_array('auto_relock',$additional_field_value)){
                            // If auto_relock is true and all others are not selected, then system is Senseon One.
                            $senseonOne = $this->getAttributeOptionId('system_compatibility','Senseon One'); 
                            $collection->addAttributeToFilter('system_compatibility',array('finset' => array($senseonOne)));                            
                        }
                        
                        
                        
                        
                        if((count($additional_field_value)>=1 && in_array('integration_with_3rd_party_system',$additional_field_value))){
                            // "If lock_status_feedback and intergration_3rd_party is true, system is Senseon Core. Select only locks from rows 9 - 61 (B locks) and add 1 ABLEL-120 per number_of_openings
                            //If auto_relock is true and intergration_3rd_party is also true, system is Senseon Core. Select only locks from rows 9 - 61 (B locks) and add 1 ABLEL-120 per number_of_openings
                            //If lock_status_feedback and intergration_3rd_party is true, and dual_authentication, discrete_access, or alarm_and_alert is also true system is Senseon Core. Select only locks from rows 9 - 61 (B locks) and add 1 ABLEL-120 per number_of_openings"
                            $senseonCore = $this->getAttributeOptionId('system_compatibility','Senseon Core'); 
                            $collection->addAttributeToFilter('system_compatibility', array('finset' => array($senseonCore)));
                            
                        }
                        
                        if( (count($additional_field_value)>=2 && (in_array('auto_relock',$additional_field_value) && !in_array('integration_with_3rd_party_system',$additional_field_value)))
                            || (count($additional_field_value)>=1 && !in_array('auto_relock',$additional_field_value) && !in_array('integration_with_3rd_party_system',$additional_field_value))
                        ){
                            //If auto_relock is true and dual_authentication, discrete_access, alarm_and_alert,
                            //lock_status_feedback, are also true, then system is Senseon Plus.
                            
                            //If dual_authentication, discrete_access, or alarm_and_alert is true system is Senseon Plus.
                            //Select only locks from rows 9 - 61 (B locks) and components from rows 126 - 146.
                            
                            $senseonPlus = $this->getAttributeOptionId('system_compatibility','Senseon Plus');                            
                            $collection->addAttributeToFilter('system_compatibility', array('finset' => array($senseonPlus)));                            
                        }
                        
                        
                        
                       
                    }
                }
            }
        }
        
        if(empty($additional_datas) && $type!='sliding_cabinet_door'){
            //If no features selected then system is Senseon One.
            $senseonOne = $this->getAttributeOptionId('system_compatibility','Senseon One'); 
            $collection->addAttributeToFilter('system_compatibility',array('finset' => array($senseonOne)));
        }
        
        return $collection;
    }
    
    
    /*
     * get product details by id
     * @param integer $id
     * @return \Magento\Catalog\Model\ProductFactory
    **/    
    
    public function getProductdetails($id) {
        return $this->_productFactory->create()->load($id);
    }
    
    
    /*
     * get product details by sku
     * @param string $sku
     * @return \Magento\Catalog\Model\ProductFactory
    **/   
    
    public function getProductBySku($sku) {
        return $this->_productFactory->create()->loadByAttribute('sku',$sku);
    }
    
    
    
    /*
     * get product image
     * @param \Magento\Catalog\Model\Product
     * @return string $imageUrl
    **/   
    
    public function getImageUrl($product) {
        
        $imageUrl = $this->_imgObj->init($product, 'product_page_image_small')
                ->setImageFile($prd->getSmallImage()) // image,small_image,thumbnail
                ->resize(380)
                ->getUrl();
        return $imageUrl;
    }
    
    
    
    /*
     * get Currency Format
     * @param float $price
     * @return string
    **/   
    
    public function getCurrencyFormat($price) {     
        return $this->priceHelper->currency($price, true, false);
    }
    
    
    /*
     * Get logo
     */
    
    public function getLogo(){
        $folderName = \Int\Configurator\Model\Config\Backend\Image::UPLOAD_DIR;
        $storeLogoPath = $this->_scopeConfig->getValue(
            'configurator/general/bom_logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        $path = $folderName . '/' . $storeLogoPath;
        
        $logoPath = $this->_directory->getPath("media")."/".$path; 
        
        
        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
        $data = file_get_contents($logoPath);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    
    }
    
    
    
    
    /*
     * Get system logo
     * @param string $system
     * @return string $base64
     **/
    
    public function getSystemLogo($system){
                
        $system = str_replace('-','_',$system);
        $folderName = \Int\Configurator\Model\Config\Backend\Image::UPLOAD_DIR;
        $storeLogoPath = $this->_scopeConfig->getValue(
            'configurator/general/'.$system,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $storeLogoPath;
        
        $logoPath = $this->_directory->getPath("media")."/".$path; 
        
        
        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
        $data = file_get_contents($logoPath);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    
    }
    
    /*
     * Get system logo
     * @param string $system
     * @return string $logourl
     **/
    
    public function getSystemLogoUrl($system){
        
        $system = str_replace('-','_',$system);
        $folderName = \Int\Configurator\Model\Config\Backend\Image::UPLOAD_DIR;
        $storeLogoPath = $this->_scopeConfig->getValue(
            'configurator/general/'.$system,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $logo = $folderName . '/' . $storeLogoPath;        
        
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                );
        
        $logourl = $mediaUrl."/".$logo;
        return $logourl;
    
    }
    /*
     * Add Products To BOM List
     * @param string $sku
     * @param integer $qty
     **/
    
    public function addProductsToBOMList($sku,$qty=1){
        $product = $this->_productFactory->create()->loadByAttribute('sku', $sku);
        $systemCompatibilities = $product->getSystemCompatibility();
        $systemCompatibilities = explode(',',$systemCompatibilities);;
        $is_senseon_plus = false;
        if(!empty($systemCompatibilities)){
            foreach($systemCompatibilities as $systemCompatibility){
                $system_compatibility = $this->getAttributeOptionText('system_compatibility',$systemCompatibility);
                if($system_compatibility == 'Senseon Plus'){
                    $is_senseon_plus = true;
                }
            }
        }
        
        
        $rowTotal = $product->getFinalPrice()*$qty;
        
        $_productItem = array();                                    
        $_productItem['qty'] = $qty;                                                    
        $_productItem['id'] = $product->getId();                                
        $_productItem['name'] = $product->getName();
        $_productItem['sku'] = $product->getSku();
        $_productItem['uom'] = $product->getAttributeText('uom');;
        $_productItem['price'] = $this->getCurrencyFormat($product->getPrice());
        $_productItem['row_total'] = $rowTotal;
        $_productItem['row_total_formatted'] = $this->getCurrencyFormat($rowTotal);
        $_productItem['is_salable'] = $product->getProductForSales();        
        $this->_products[$this->_fixtureId][$product->getSku()] = $_productItem;
    }
    
    
    /*
     * Get selected systems
     * @param integer $configuratorId
     * @return array $_systems
     **/
    
    public function getSelectedSystems($configuratorId){
        
        $configurator = $this->_configuratorFactory->create()->load($configuratorId);
       
        $_systems = array();
      
        $fixtureDetails = $this->getFixtureDetails($configurator->getId());
        //$_products = array();
        foreach($fixtureDetails as $key=>$fixturecol) {
            $fixtureId = $fixturecol['fixture_id'];           
            $additionalData = unserialize($fixturecol['additional_data']);
        
            if(!empty($additionalData)){
                foreach($additionalData as $additional_data){
                    $additional_fields = isset($additional_data['attributes_fields_data'])?$additional_data['attributes_fields_data']:array();
                    
                    if(!empty($additional_data)){
                    
                        foreach($additional_fields as $additional_field){
                            
                            $additional_field_values = isset($additional_field['attribute_option_value'])?$additional_field['attribute_option_value']:array();
                            $additional_field_values = array_map('trim', explode(',', $additional_field_values));
                            $additional_field_value = array_filter($additional_field_values);
                            $additional_field_value = $this->getAttributeAllOptions('additional_features',$additional_field_value);
                      
                            if(count($additional_field_value)==0
                               || count($additional_field_value)==1  && in_array('auto_relock',$additional_field_value)
                            ){
                                $_systems[] = 'senseon-one';
                            }
                        
                        
                            if((count($additional_field_value)>=1 && in_array('integration_with_3rd_party_system',$additional_field_value))){
                                $_systems[] = 'senseon-core';                                
                            }
                        
                            if( (count($additional_field_value)>=2 && (in_array('auto_relock',$additional_field_value) && !in_array('integration_with_3rd_party_system',$additional_field_value)))
                             || (count($additional_field_value)>=1 && !in_array('auto_relock',$additional_field_value) && !in_array('integration_with_3rd_party_system',$additional_field_value))
                            ){
                                $_systems[] = 'senseon-plus';                          
                            }
                        
                       
                        }
                    }
                }
            }else{
                $_systems[] = 'senseon-one';
            }
        }
        
        $_systems = array_filter(array_unique($_systems));
        
        return $_systems;
    }
    
    
    
    /*
     * Get selected systems by fixture Id
     * @param integer $fixtureId
     * @return array $_systems
     **/
    
    public function getSelectedSystemsByFixtureId($fixtureId){
        
        $_fixture = $this->_fixtureFactory->create()->load($fixtureId);       
        $additionalData = unserialize($_fixture->getAdditionalData());
        $_systems = '';
        if(!empty($additionalData)){
            foreach($additionalData as $additional_data){
                $additional_fields = isset($additional_data['attributes_fields_data'])?$additional_data['attributes_fields_data']:array();
                
                if(!empty($additional_data)){
                
                    foreach($additional_fields as $additional_field){
                        
                        $additional_field_values = isset($additional_field['attribute_option_value'])?$additional_field['attribute_option_value']:array();
                        $additional_field_values = array_map('trim', explode(',', $additional_field_values));
                        $additional_field_value = array_filter($additional_field_values);
                        $additional_field_value = $this->getAttributeAllOptions('additional_features',$additional_field_value);
                  
                        if(count($additional_field_value)==0
                           || count($additional_field_value)==1  && in_array('auto_relock',$additional_field_value)
                        ){
                            $_systems = 'senseon-one';
                        }
                    
                    
                        if((count($additional_field_value)>=1 && in_array('integration_with_3rd_party_system',$additional_field_value))){
                            $_systems = 'senseon-core';                                
                        }
                    
                        if( (count($additional_field_value)>=2 && (in_array('auto_relock',$additional_field_value) && !in_array('integration_with_3rd_party_system',$additional_field_value)))
                            || (count($additional_field_value)>=1 && !in_array('auto_relock',$additional_field_value) && !in_array('integration_with_3rd_party_system',$additional_field_value))
                        ){
                            $_systems = 'senseon-plus';                          
                        }
                    
                   
                    }
                }else{
                    $_systems = 'senseon-one';
                }
            }
        }else{
            $_systems = 'senseon-one';
        }
        
        return $_systems;
    }
    
    
    /*
     * Check if product is eligible for add to BDM
     * @param string $sku
     * @param integer $fixtureId
     * @return boolean $canAdd
     **/
    
    public function canAddProductToBom($sku,$fixtureId){
               
        $canAdd = false;        
        $currentSystem = $this->getSelectedSystemsByFixtureId($fixtureId);
        $_product = $this->getProductBySku($sku);
        $_productSystems = $_product->getSystemCompatibility();
        $_productSystems = explode(',',$_productSystems);
        
        foreach($_productSystems as $_productSystems){
            $system_compatibility_attr = $_product->getResource()->getAttribute('system_compatibility');
            if ($system_compatibility_attr->usesSource()) {
                $productSystem = $system_compatibility_attr->getSource()->getOptionText($_productSystems);
                $productSystem = str_replace(' ', '-', trim(strtolower($productSystem)));
               
                if($productSystem == $currentSystem){                    
                    $canAdd = true;
                    break;
                }
            }
        }
        return $canAdd;
        
    }
    
    
    
    /*
     * get all options by attribute code
     * @param string $attributeCode
    **/
    
    public function getAttributeAllOptions($attributeCode, $additionalFieldValues){
        //$attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        //$options = $attribute->getSource()->getAllOptions();
        $labels = array();
        $productReource=$this->_productResourceModel->create();
        $attribute = $productReource->getAttribute($attributeCode);
        
        if ($attribute->usesSource()) {
            $i = 0;
            $this->_storeManager->getGroup();
            foreach($additionalFieldValues as $additionalFieldValue){
                $optionLabel = $this->getAttributeOptionText($attributeCode,$additionalFieldValue);
                $optionLabel = str_replace(' ', '_', trim(strtolower($optionLabel)));
                $labels[++$i] = $optionLabel;
            }
        }
        return $labels;
    }
    
    
    
    
    public function getAttributeOptionText($attributeCode,$optionId){
        
      
        $productReource=$this->_productResourceModel->create();
        $attribute = $productReource->getAttribute($attributeCode);
        
        if ($attribute->usesSource()) {
               return $attribute->getSource()->getOptionText($optionId);
        }
        
        return false;
    }
    
    
    public function getAttributeOptionId($attribute,$label)
    {
        $productReource=$this->_productResourceModel->create();
        $attr = $productReource->getAttribute($attribute);
        if ($attr->usesSource()) {
               return  $option_id = $attr->getSource()->getOptionId($label);
        }
         
        return false;
    }
    
    
}