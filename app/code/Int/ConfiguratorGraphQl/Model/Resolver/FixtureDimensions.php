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
use Int\Configurator\Model\OpeningTypesFactory as OpeningTypeModel;
use Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory as OpeningTypeCollection;
/**
 * Class FixtureDimensions
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class FixtureDimensions implements ResolverInterface
{
 
    protected $_fixtureFactory;
    protected $_fixture;
    protected $_openingTypesModel;
    protected $_openingTypesCollection;


    public function __construct(
        \Int\Configurator\Model\FixtureFactory $fixtureFactory,
        \Int\Configurator\Model\Fixture $fixture,
		OpeningTypeModel $openingTypesModel,
        OpeningTypeCollection $openingTypesCollection
    ) {
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_fixture  = $fixture;
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
		
		
		$fixture_id = $args['input']['fixture_id'];
		$height = $args['input']['height'];
		$width = isset($args['input']['width'])?$args['input']['width']:'';	
		$opening_id = isset($args['input']['opening_id'])?$args['input']['opening_id']:'';	
		
		$fixture = $this->_fixtureFactory->create()->load($fixture_id);		
		$fixture_width = $fixture->getFixtureLength();
		$fixture_height = $fixture->getFixtureHeight();
		
		$total_height = 0;
		if($width != '') {
			if($width >  (float)$fixture_width) {
							
				$resultData['message'] = "Your input width is exceeded from fixture width ";
				$resultData['status'] = 0;
				return $resultData;
			}
		}
		
		$openingCollection = $this->_openingTypesCollection->create()->addFieldToFilter('fixture_id',$fixture_id)->getData();
		foreach($openingCollection as $options) {
			$product_data = unserialize($options['product_data']);
			$custom_fields_data = $product_data['custom_fields_data'];
			
			if($opening_id == $options['opening_type_id']) {
				continue;
			}
            if(!empty($custom_fields_data)){
                foreach($custom_fields_data as $custom_fields_values) {
                    if($custom_fields_values['field_name'] == 'height') {
                        $total_height = (float) $total_height + (float) $custom_fields_values['field_value'];
                    }				
                }
            }
		
		}
		
		$total_height = $total_height + (float)$height;
		
		if($total_height > $fixture_height) {
			$resultData['message'] = "Your input height is exceeded from fixture height";
			$resultData['status'] = 0;
		}
		else{
			$resultData['message'] = "correct";
			$resultData['status'] = 1;
		}
		
		
		return $resultData;
        
    }

}