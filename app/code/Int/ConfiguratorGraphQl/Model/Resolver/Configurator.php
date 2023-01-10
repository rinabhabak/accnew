<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\ConfiguratorGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

/**
 * Class Configurator
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class Configurator implements ResolverInterface
{

    private $_configuratorDataProvider;
    protected $_configuratorFactory;
    protected $_fixtureFactory;
    protected $_openingTypesFactory;
    protected $_openingTypesCollectionFactory;

    /**
     * @param Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Configurator $configuratorDataProvider
     */
    public function __construct(
        \Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Configurator $configuratorDataProvider,
        \Int\Configurator\Model\ConfiguratorFactory $configuratorFactory,
        \Int\Configurator\Model\FixtureFactory $fixtureFactory,
        \Int\Configurator\Model\OpeningTypesFactory $openingTypesFactory,
        \Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory $openingTypesCollectionFactory
    ) {
        $this->_configuratorFactory  = $configuratorFactory;
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_openingTypesFactory  = $openingTypesFactory;
        $this->_openingTypesCollectionFactory = $openingTypesCollectionFactory;
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
        
        //if (false === $context->getExtensionAttributes()->getIsCustomer()) {
        //    throw new GraphQlAuthorizationException(
        //        __('The current customer isn\'t authorized.')
        //    );
        //}
        
        $output = array();
        $configuratorId = $args['configurator_id'];
        $configurator = $this->_configuratorFactory->create()->load($configuratorId);
        if(!$configurator->getId()){
            throw new \Exception('Invalid configurator.');
        }
     
        $output = $configurator->getData();
        
        if($configurator->getId()){
            $output['fixtures'] = array();
            $fixtures = $this->_fixtureFactory->create()->getCollection();
            $fixtures->addFieldToFilter('configurator_id',$configuratorId);
            //$output['fixtures'] = $fixtures->getData();
            foreach($fixtures as $fixture){
                $_fixtureId = $fixture->getId();
                $output['fixtures'][$_fixtureId] = $fixture->getData();
                
                $_openingTypes = $this->_openingTypesCollectionFactory->create()
                                ->addFieldToFilter('fixture_id',$_fixtureId);
        
                if(count($_openingTypes->getAllIds())){
    
                    foreach ($_openingTypes as $key => $_openingType) { 
                        $_openingTypeItem = array();
                        $_openingTypeItem['opening_type_id'] = $_openingType->getId();
                        $_openingTypeItem['attribute_option_id'] = $_openingType->getAttributeOptionId();
                        $_openingTypeItem['fixture_id'] = $_openingType->getFixtureId();
                        $_openingTypeItem['name'] = $_openingType->getName();
                        $_openingTypeItem['status'] = $_openingType->getStatus();
                        $_openingTypeItem['product_data'] = $_openingType->getProductData();
                        $_openingTypeItem['same_as'] = $_openingType->getProductData();
                        $_openingTypeItem['created_at'] = $_openingType->getCreatedAt();
                        $_openingTypeItem['updated_at'] = $_openingType->getUpdatedAt();
                        //$_openingTypesData['opening_types'][] = $_openingTypeItem;
                         $output['fixtures'][$_fixtureId]['opening_types'][$_openingType->getId()] = $_openingTypeItem;
                    }
                    
                   
                    
                }
                
                
            }
        }
        
       // print_r($output);
        
        
        return $output;
    }

}