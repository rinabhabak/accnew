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
 * Class DependencyAttributes
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class DependencyAttributes implements ResolverInterface
{
    
    protected $eavConfig;
	
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
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
        
		if(!isset($args['attribute_code']) || !isset($args['option_id'])) {
			throw new GraphQlAuthorizationException(
                __('Attribute code and option id is required.')
            );
		}
		$attributes = array();
		if($args['attribute_code'] == 'drawer_locking_solution') {
            
            $optionlabel =  $this->getAttributeOptionText('drawer_locking_solution',$args['option_id']);            
			$attribute = $this->eavConfig->getAttribute('catalog_product', 'drawer_lock_function');
			$options = $attribute->getSource()->getAllOptions();    

			if($optionlabel == 'Undermount') {
				$values = array();
			
				foreach($options as $attributeValues) {
					if($attributeValues['label']=='Easy Close' || $attributeValues['label']=='Touch Release'){
						$values = array();
						$values['attribute_code'] = $attribute->getAttributeCode();
						$values['option_id'] = (int)$attributeValues['value'];
						$values['option_label'] = $attributeValues['label'];
						$values['attribute_label'] = $attribute->getFrontendLabel();
						$values['attribute_id'] = (int)$attribute->getId();
						$attributes[] = $values;
					}
				}
				return $attributes;
				
			}
			
			if($optionlabel == 'Sidemount') {
				$values = array();
				

				foreach($options as $attributeValues) {
					if($attributeValues['label']!='Easy Close' && $attributeValues['value']!=0){
						$values = array();
						$values['attribute_code'] = $attribute->getAttributeCode();
						$values['option_id'] = (int)$attributeValues['value'];
						$values['option_label'] = $attributeValues['label'];
						$values['attribute_label'] = $attribute->getFrontendLabel();
						$values['attribute_id'] = (int)$attribute->getId();
						$attributes[] = $values;
					}
				}

				return $attributes;
			}
            
            
            if($optionlabel == 'Standalone' ) {
				$values = array();
				
				foreach($options as $attributeValues) {
					if($attributeValues['value']!=0){
						$values = array();
						$values['attribute_code'] = $attribute->getAttributeCode();
						$values['option_id'] = (int)$attributeValues['value'];
						$values['option_label'] = $attributeValues['label'];
						$values['attribute_label'] = $attribute->getFrontendLabel();
						$values['attribute_id'] = (int)$attribute->getId();
						$attributes[] = $values;
					}
				}
				
				return $attributes;
			}
		}
		
		if($args['attribute_code'] == 'door_hinge_type') {
			
            $optionlabel =  $this->getAttributeOptionText('door_hinge_type',$args['option_id']);
            
			$attribute = $this->eavConfig->getAttribute('catalog_product', 'hinged_door_lock_function');
			$options = $attribute->getSource()->getAllOptions();
			
			
			if($optionlabel == 'Free Swinging' ) {
				$values = array();
				foreach($options as $attributeValues) {
					if($attributeValues['value'] == '' || $attributeValues['label'] == 'Easy Close') { continue; }
						$values = array();
						$values['attribute_code'] = $attribute->getAttributeCode();
						$values['option_id'] = $attributeValues['value'];
						$values['option_label'] = $attributeValues['label'];
						$values['attribute_label'] = $attribute->getFrontendLabel();
						$values['attribute_id'] = (int)$attribute->getId();
						$attributes[] = $values;
				}			
				return $attributes;			
			}
            
			if($optionlabel == 'Easy Close') {
				$values = array();
				foreach($options as $attributeValues) {
					if($attributeValues['value'] == '') { continue; }
						$values = array();
						$values['attribute_code'] =$attribute->getAttributeCode();
						$values['option_id'] = (int)$attributeValues['value'];
						$values['option_label'] = $attributeValues['label'];
						$values['attribute_label'] = $attribute->getFrontendLabel();
						$values['attribute_id'] = (int)$attribute->getId();
						$attributes[] = $values;
				}			
				return $attributes;	
			}
		}
    }
    
    
    
    public function getAttributeOptionText($attributeCode,$optionId){
        
        $drawerLockFunctionAttribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        $optionlabel =  $drawerLockFunctionAttribute->getSource()->getOptionText($optionId);
        return $optionlabel;
    }

}