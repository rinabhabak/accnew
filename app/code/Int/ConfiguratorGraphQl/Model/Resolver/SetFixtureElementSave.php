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
use Int\Configurator\Model\OpeningTypesFactory as OpeningTypeModel;
use Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory as OpeningTypeCollection;

/**
 * Class SetFixtureElementSave
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class SetFixtureElementSave implements ResolverInterface
{

    /**
     * @var OpeningTypeModel
     */
    protected $_openingTypesModel;

    /**
     * @var OpeningTypeCollection
     */
    protected $_openingTypesCollection;

    /**
     * @param OpeningTypeModel $openingTypesModel
     * @param OpeningTypeCollection $openingTypesCollection
     */
    public function __construct(
        OpeningTypeModel $openingTypesModel,
        OpeningTypeCollection $openingTypesCollection
    ){
        $this->_openingTypesModel = $openingTypesModel;
        $this->_openingTypesCollection = $openingTypesCollection;
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
            
            $_response = array();

            
            if (!isset($args['input']['fixture_id']) || empty($args['input']['fixture_id'])) {
                throw new GraphQlInputException(__('Fixture id is require.'));
            }

            $fixture_id = $args['input']['fixture_id'];
            $_response['fixture_id'] = $fixture_id; 

            $same_as_datas = $args['input']['same_as_data'];
            if(empty($same_as_datas)){
                throw new GraphQlInputException(__('Invalid Data'));
            }
           
            foreach($same_as_datas as $same_as_data) {

                $opening_type_id_old = isset($same_as_data['same_as_opening_type_id'])?$same_as_data['same_as_opening_type_id']:'0';
                $opening_type_id_new = $same_as_data['current_opening_type_id'];
                if($opening_type_id_old > 0){
                    $openingModel = $this->getCollectionByFilter($fixture_id, $opening_type_id_old);
                    $oldProductData = unserialize($openingModel->getData()[0]['product_data']);
                    $oldFixtureType = $openingModel->getData()[0]['attribute_option_id'];
    
                    $_response['same_as_data'][$opening_type_id_new]['same_as_opening_type_id'] = $opening_type_id_old;
                    $_response['same_as_data'][$opening_type_id_new]['current_opening_type_id'] = $opening_type_id_new;
    
                    if($openingModel->count() < 1){                    
                        $_response['same_as_data'][$opening_type_id_new]['message'] = __('No data found. Please try again.');
                        continue;
                    }
    
                    $model = $this->_openingTypesModel->create()->load($opening_type_id_new);
                    if($model->getAttributeOptionId() != $oldFixtureType){                   
                        $_response['same_as_data'][$opening_type_id_new]['message'] = __('Both are diffrent fixtures');
                        continue;
                    }
                    $model->setProductData(serialize($oldProductData))
                        ->setStatus(\Int\Configurator\Model\OpeningTypes::STATUS_COMPLETE)
                        ->setIsSame($opening_type_id_old)
                        ->save();
                    $_response['same_as_data'][$opening_type_id_new]['message'] = true;
					
					
                }else{
                    $model = $this->_openingTypesModel->create()->load($opening_type_id_new);
                    $_response['same_as_data'][$opening_type_id_new]['same_as_opening_type_id'] = $opening_type_id_old;
                    $_response['same_as_data'][$opening_type_id_new]['current_opening_type_id'] = $opening_type_id_new;
                    $model->setIsSame($opening_type_id_old)->save();
                    $_response['same_as_data'][$opening_type_id_new]['message'] = true;
                }

                                             
                

            }
            

        } catch (\Exception $e) {
            $_response['same_as_data']['message'] = _($e->getMessage());
        }
        
        return $_response;
    }

    private function getCollectionByFilter($fixture_id, $opening_type_id_old)
    {
        $collection = $this->_openingTypesCollection->create();

        if(!empty($fixture_id)){
            $collection->addFieldToFilter('fixture_id', $fixture_id);
        }

        if(!empty($opening_type_id_old)){
            $collection->addFieldToFilter('opening_type_id', $opening_type_id_old);
        }

        $collection->getFirstItem();

        return $collection;
    }

}
