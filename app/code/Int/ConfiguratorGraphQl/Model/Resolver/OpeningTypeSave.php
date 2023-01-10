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

/**
 * Class ConfiguratorSave
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class OpeningTypeSave implements ResolverInterface
{

    private $_configuratorDataProvider;
    protected $_configuratorFactory;
    protected $_configurator;
    protected $_fixtureFactory;
    protected $_openingTypesFactory;
    protected $_openingTypesCollectionFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
	protected $timezone;

    /**
     * @param Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Configurator $configuratorDataProvider
     */
    public function __construct(
        \Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Configurator $configuratorDataProvider,
        \Int\Configurator\Model\ConfiguratorFactory $configuratorFactory,
        \Int\Configurator\Model\Configurator $configurator,
        \Int\Configurator\Model\FixtureFactory $fixtureFactory,
        \Int\Configurator\Model\OpeningTypesFactory $openingTypesFactory,
        \Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory $openingTypesCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone

    ) {
        $this->_configuratorFactory  = $configuratorFactory;
        $this->_configurator  = $configurator;
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_openingTypesFactory  = $openingTypesFactory;
        $this->_openingTypesCollectionFactory = $openingTypesCollectionFactory;
        $this->timezone = $timezone;

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

        try{
            $_openingTypesData  = array();
            $fixtureOpeningTypesData  = array();
			$configuratorId = '';
            if(!isset($args['input']['fixture_id'])){
                throw new \Exception('Fixture Id is require.');
            }


            $fixture = $this->_fixtureFactory->create();
            if(isset($args['input']['fixture_id'])){
                $fixture = $fixture->load($args['input']['fixture_id']);
                $fixtureOpeningTypesData = unserialize($fixture->getOpeningTypesData());
            }

            $fixtureOpeningTypes = array();

            $openingTypesInputs = $args['input']['opening_types'];
           
            foreach ($openingTypesInputs as $openingTypesInput){

                $attribute_option_id = $openingTypesInput['attribute_option_id'];                
                $fixtureOpeningTypes[$attribute_option_id]['attribute_id'] =  $openingTypesInput['attribute_id'];
                $fixtureOpeningTypes[$attribute_option_id]['attribute_code'] = $openingTypesInput['attribute_code'];
                $fixtureOpeningTypes[$attribute_option_id]['attribute_option_id'] = $attribute_option_id;
                $fixtureOpeningTypes[$attribute_option_id]['attribute_option_label'] = $openingTypesInput['attribute_option_label'];
                $fixtureOpeningTypes[$attribute_option_id]['quantity'] = $openingTypesInput['quantity'];
            }



            $fixture->setOpeningTypesData(serialize($fixtureOpeningTypes))->save();

            $_fixture = $fixture->load($fixture->getId());
			$configuratorId = $_fixture->getConfiguratorId();
            $_fixtureId = $fixture->getId();
            $_openingTypesInputs = $_fixture->getOpeningTypesData();
            $_openingTypesInputs = unserialize($_openingTypesInputs);

            if(!empty($_openingTypesInputs)){
                foreach ($_openingTypesInputs as $attribute_option_id => $_openingTypesInput) {

                    $qty = $_openingTypesInput['quantity'];

                    $_openingTypesCollection = $this->_openingTypesCollectionFactory->create()
                                                    ->addFieldToFilter('attribute_option_id',$attribute_option_id)
                                                    ->addFieldToFilter('fixture_id',$_fixtureId);
                    $_openingTypesCollection = $_openingTypesCollection->setOrder('fixture_id','desc')->setCurPage(1);
                    $existingTypes = count($_openingTypesCollection->getAllIds());
                    if($existingTypes<=0){

                        
                        for ($i=1; $i <= $qty; $i++) { 
                            $opening_types_data = array();
                            $opening_types_data['fixture_id'] = $_fixtureId;
                            $opening_types_data['attribute_option_id'] = $_openingTypesInput['attribute_option_id'];
                            $opening_types_data['name'] = __('%1 %2', $_openingTypesInput['attribute_option_label'],$i);
                            $opening_types_data['created_at'] = $this->timezone->date()->format('Y-m-d H:i:s');
                            $opening_types_data['updated_at'] = $this->timezone->date()->format('Y-m-d H:i:s');

                            $_openingTypes = $this->_openingTypesFactory->create();
                            $_openingTypes->setData($opening_types_data)->save();
                        }

                    }else{

                        if($_openingTypesInput['quantity'] > $existingTypes){

                            $numbber_of_types = $_openingTypesInput['quantity'];
                            $j = ($existingTypes+1);

                            for ($i=$j; $i <= $numbber_of_types; $i++) { 

                                $opening_types_data = array();
                                $opening_types_data['fixture_id'] = $_fixtureId;
                                $opening_types_data['attribute_option_id'] = $_openingTypesInput['attribute_option_id'];
                                $opening_types_data['name'] = __('%1 %2',$_openingTypesInput['attribute_option_label'],$i);
                                $opening_types_data['created_at'] = $this->timezone->date()->format('Y-m-d H:i:s');
                                $opening_types_data['updated_at'] = $this->timezone->date()->format('Y-m-d H:i:s');
                                $opening_types_data['status'] = \Int\Configurator\Model\OpeningTypes::STATUS_COMPLETE;

                                $_openingTypes = $this->_openingTypesFactory->create();
                                $_openingTypes->setData($opening_types_data)->save();
                            }

                        }else{

                            if($_openingTypesInput['quantity'] != $existingTypes){

                                $totalDelete = $existingTypes - $_openingTypesInput['quantity'];

                                if($totalDelete>0){
                                    $_openingTypesCollection = $this->_openingTypesCollectionFactory->create()
                                                                    ->addFieldToFilter('attribute_option_id',$attribute_option_id)
                                                                    ->setOrder('opening_type_id','desc')
                                                                    ->addFieldToFilter('fixture_id',$_fixtureId)
                                                                    ->setPageSize($totalDelete)->setCurPage(1);
                                    
                                    foreach ($_openingTypesCollection as $_openingTypes) {
                                        $_openingTypes->delete();
                                    }
                                }
                                
                            }

                        }


                    }




                }
            }


            $_openingTypesData['opening_types'] = array();

            $_openingTypes = $this->_openingTypesCollectionFactory->create()
                            ->addFieldToFilter('fixture_id',$_fixtureId);

            if(count($_openingTypes->getAllIds())){
				
				$_configurator = $this->_configuratorFactory->create()->load($configuratorId);
				if(count($_openingTypes->getAllIds()) > 15) {
					$_configurator->setIsConsulatativeSale(1);
					$_configurator->save();
				}
				else {
					$_configurator->setIsConsulatativeSale(0);
					$_configurator->save();
				}
				
                foreach ($_openingTypes as $key => $_openingType) { 
                    $_openingTypeItem = array();
                    $_openingTypesData['opening_types'][$_openingType->getId()]['opening_type_id'] = $_openingType->getId();
                    $_openingTypesData['opening_types'][$_openingType->getId()]['attribute_option_id'] = $_openingType->getAttributeOptionId();
                    $_openingTypesData['opening_types'][$_openingType->getId()]['fixture_id'] = $_openingType->getFixtureId();
                    $_openingTypesData['opening_types'][$_openingType->getId()]['name'] = $_openingType->getName();
                    $_openingTypesData['opening_types'][$_openingType->getId()]['product_data'] = $_openingType->getProductData();
                    $_openingTypesData['opening_types'][$_openingType->getId()]['same_as'] = $_openingType->getProductData();
                    $_openingTypesData['opening_types'][$_openingType->getId()]['created_at'] = $_openingType->getCreatedAt();
                    $_openingTypesData['opening_types'][$_openingType->getId()]['updated_at'] = $_openingType->getUpdatedAt();
                    //$_openingTypesData['opening_types'][] = $_openingTypeItem;
                }
                
            }

            return $_openingTypesData;

        } catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
        }
                
        
    }

}
