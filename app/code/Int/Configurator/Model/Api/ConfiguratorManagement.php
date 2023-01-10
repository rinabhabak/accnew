<?php

namespace Int\Configurator\Model\Api;

class ConfiguratorManagement implements \Int\Configurator\Api\ConfiguratorManagementInterface
{
   
    protected $_configuratorFactory;
    protected $_fixtureFactory;
    protected $_unityFactory;
    protected $_openingTypesFactory;
    protected $_openingTypesCollectionFactory;
    protected $optionFactory;
	protected $request;
    
    /*
     * \Int\Configurator\Model\ConfiguratorFactory $configuratorFactory
     * \Int\Configurator\Model\FixtureFactory $fixtureFactory
	 * \Int\Configurator\Model\UnityFactory $unityFactory,
     * \Int\Configurator\Model\OpeningTypesFactory $openingTypesFactory
     * \Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory $openingTypesCollectionFactory
     * \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory
    **/

    public function __construct(
        \Int\Configurator\Model\ConfiguratorFactory $configuratorFactory,
        \Int\Configurator\Model\FixtureFactory $fixtureFactory,
        \Int\Configurator\Model\UnityFactory $unityFactory,
        \Int\Configurator\Model\OpeningTypesFactory $openingTypesFactory,
        \Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory $openingTypesCollectionFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
		\Magento\Framework\App\RequestInterface $request

    ) {
        $this->_configuratorFactory = $configuratorFactory;
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_unityFactory  = $unityFactory;
        $this->_openingTypesFactory  = $openingTypesFactory;
        $this->_openingTypesCollectionFactory = $openingTypesCollectionFactory;
        $this->optionFactory = $optionFactory;
		$this->request = $request;
    }

    /**
     * get Configurator Api data.
     * @api
     * @param int $configurator_id
     * @param int $fixture_id
     * @return string
     */
    
    public function getPreview($configurator_id,$fixture_id)
    {	
	
        try {
			$output = array();
            $configurator = $this->_configuratorFactory->create()
                ->load($configurator_id);

            if (!$configurator->getId()) {
               $returnArray['error'] = 'No configuration found. Please Create new configuration.';
			   $output[]=$returnArray;
				return $output;
            }
         
			
            $_openingTypes = $this->_openingTypesCollectionFactory->create()
                                    ->addFieldToFilter('fixture_id',$fixture_id);
			$fixtureDetails = $this->_fixtureFactory->create()->load($fixture_id);						
                    if(count($_openingTypes->getAllIds())){
						
                        foreach ($_openingTypes as $key => $_openingType) { 
								$_openingTypeItem = array();
								
								$_openingTypeItem['configurator_id'] = $configurator_id;
								$_openingTypeItem['fixture_id'] = $_openingType->getFixtureId();
								$_openingTypeItem['fixture_length'] = $fixtureDetails->getFixtureLength();
								$_openingTypeItem['fixture_depth'] = $fixtureDetails->getFixtureDepth();
								$_openingTypeItem['fixture_height'] = $fixtureDetails->getFixtureHeight();
								$_openingTypeItem['opening_type_id'] = $_openingType->getId();
								$_openingTypeItem['name'] = $_openingType->getName();
								$productDataCollection = array();
								
								if($_openingType->getProductData() !='') {
									$serializeProductData  = unserialize($_openingType->getProductData());					

									$attributes_fields_data = $serializeProductData['attributes_fields_data'];
									foreach ($attributes_fields_data as $key => $value) {
										$productDataCollection[$value['attribute_code']] = $value['attribute_option_label'];
									}

									$custom_fields_data = $serializeProductData['custom_fields_data'];
									foreach ($custom_fields_data as $key => $value) {
										$productDataCollection[$value['field_name']] = $value['field_value'];									
									}					
		
									$_openingTypeItem['product_data'] = $productDataCollection;
								}
								else{
									$_openingTypeItem['product_data'] = '';
								}
								$_openingTypeItem['same_as'] = $_openingType->getIsSame();
								$_openingTypeItem['created_at'] = $_openingType->getCreatedAt();
								$_openingTypeItem['updated_at'] = $_openingType->getUpdatedAt();
								$output[]=$_openingTypeItem;
							
                        }             
                        
                    }
					else{
						$returnArray['error'] = 'No configuration found. Please Create new configuration.';
					    $output[]=$returnArray;
						
					}
					
					return $output;
					
		  
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 0;
			return $returnArray;
        } catch (\Exception $e) {
            //$this->createLog($e);
            $returnArray['error'] = __('unable to process request');
            $returnArray['status'] = 2;
            return $returnArray;;
        }
        
        
    }
	
	/**
     * set 3dConfigurator Api data.
     *
     * @api
     *
     * 
     *
     * @return mixed
     */
	 
	public function set3dConfigurators() {
		try {
			$output = array();
			$params = $this->request->getContent();
			$paramsArray = json_decode($params,true);
			$configuratorId = $paramsArray['_cid'];
			$fixtureId = $paramsArray['_fid'];
			
			$unityCollection = $this->_unityFactory->create()->getCollection()->addFieldToFilter('configurator_id', $configuratorId)->addFieldToFilter('fixture_id', $fixtureId);
			if(count($unityCollection->getData()) > 0){
				foreach($unityCollection as $data){
					$configurator_id = $data['id'];
				}
				$configuratormodel = $this->_unityFactory->create()->load($configurator_id);
			}
			else{
				$configuratormodel = $this->_unityFactory->create();
				$configuratormodel->setData('configurator_id', $configuratorId);
				$configuratormodel->setData('fixture_id', $fixtureId);
			}
			
			$configuratormodel->setData('details', $params);
			$configuratormodel->save();
			$returnArray['success'] = 'Successfully Saved 3dConfigurators.';
			$output[]=$returnArray;
		 }
		 catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
			$output[]=$returnArray;
        }
		return $output;
		
	}
	
	/**
     * get Configurator Api data.
     *
     * @api
     *
     * @param int $configurator_id
     * @param int $fixture_id
     *
     * @return string
     */
    public function getConfigurators3dPreview($configurator_id, $fixture_id){
		try{
		$configuratormodel = $this->_unityFactory->create()->getCollection()->addFieldToFilter('configurator_id', $configurator_id)->addFieldToFilter('fixture_id', $fixture_id);		
			if(count($configuratormodel->getData()) > 0){
				foreach($configuratormodel as $data){
					$configuratorData = $data['details'];
				}
				echo $configuratorData;
				exit;
			}
			else{
				$returnArray['error'] = 'No 3dconfiguration found.';
				$output[]=$returnArray;
				return $output;
			}
		}
		catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
			$output[]=$returnArray;
			return $output;
        }
	}
}