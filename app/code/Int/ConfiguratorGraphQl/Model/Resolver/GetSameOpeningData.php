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
 * Class GetSameOpeningData
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class GetSameOpeningData implements ResolverInterface
{

 
    protected $_openingTypesCollectionFactory;

    /**
     * @param \Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory $openingTypesCollectionFactory
     */
    public function __construct(        
        \Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory $openingTypesCollectionFactory
    ) {               
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
        
	
        $output = array();
        $fixture_id = $args['fixture_id'];
        $_openingTypes = $this->_openingTypesCollectionFactory->create()
                                ->addFieldToFilter('fixture_id',$fixture_id);
        
                if(count($_openingTypes->getAllIds())){
    
                    foreach ($_openingTypes as $key => $_openingType) { 
                        $_openingTypeItem = array();
                        $_openingTypeItem['type_id'] = $_openingType->getId();
                        $_openingTypeItem['same_as'] = $_openingType->getIsSame();
						$_openingTypeItem['name'] = $_openingType->getName();
                        $output[] = $_openingTypeItem;
                    }                    
                }             
        
        return $output;
    }

}