<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ConfiguratorGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Int\Configurator\Model\OpeningTypesFactory as OpeningTypeModel;
use Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory as OpeningTypeCollection;

class SaveFixturesOpeningTypes implements ResolverInterface
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
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(
                __('Your session has been expired. Please log in again.')
            );
        }

        if(empty($args['input']['fixture_id'])){
            throw new GraphQlInputException(__('Fixture is Required.'));
        }

        if(empty($args['input']['opening_type_id'])){
            throw new GraphQlInputException(__('Opening Type is Required.'));
        }

        if(empty($args['input']['attribute_option_id'])){
            throw new GraphQlInputException(__('Attribute Option is Required.'));
        }

        try{
            $fixture_id = $args['input']['fixture_id'];
            $opening_type_id = $args['input']['opening_type_id'];
            $attribute_option_id = $args['input']['attribute_option_id'];
            $attributes_fields_data = $args['input']['attributes_fields_data'];
            $custom_fields_data = $args['input']['custom_fields_data'];
            $openingModel = $this->getCollectionByFilter($fixture_id, $opening_type_id, $attribute_option_id);
            
            if($openingModel->count() < 1){
                throw new GraphQlNoSuchEntityException(__('No data found. Please try again.'));
            }

            $fixtureFileds = [];
        
            foreach ($attributes_fields_data as $attributes_field)
            {
                $attribute_id = $attributes_field['attribute_id'];

                if(!isset($attribute_id)){
                    continue;
                }

                $fixtureFileds['attributes_fields_data'][$attribute_id]['attribute_id'] =  $attribute_id;
                $fixtureFileds['attributes_fields_data'][$attribute_id]['attribute_code'] = $attributes_field['attribute_code'];
                $fixtureFileds['attributes_fields_data'][$attribute_id]['attribute_label'] = $attributes_field['attribute_label'];
                $fixtureFileds['attributes_fields_data'][$attribute_id]['attribute_option_label'] = $attributes_field['attribute_option_label'];
                $fixtureFileds['attributes_fields_data'][$attribute_id]['attribute_option_value'] = $attributes_field['attribute_option_value'];
            }

            foreach ($custom_fields_data as $key => $custom_field){
                $fixtureFileds['custom_fields_data'][$key]['field_label'] = $custom_field['field_label'];
                $fixtureFileds['custom_fields_data'][$key]['field_name'] = $custom_field['field_name'];
                $fixtureFileds['custom_fields_data'][$key]['field_value'] = $custom_field['field_value'];
            }

            if(!empty($fixtureFileds))
            {
                $model = $this->_openingTypesModel->create()->load($opening_type_id);
                $model->setProductData(serialize($fixtureFileds))
                        ->setStatus(\Int\Configurator\Model\OpeningTypes::STATUS_COMPLETE)
                        ->save();

                $productData = !empty($model->getProductData()) ? unserialize($model->getProductData()) : NULL;

                return [
                    'opening_type_id' => $model->getId(),
                    'fixture_id' => $model->getFixtureId(),
                    'attribute_option_id' => $model->getAttributeOptionId(),
                    'attributes_fields_data' => !empty($productData['attributes_fields_data']) ? $productData['attributes_fields_data'] : NULL,
                    'custom_fields_data' => !empty($productData['custom_fields_data']) ? $productData['custom_fields_data'] : NULL
                ];
            }
            
        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }

    private function getCollectionByFilter($fixture_id, $opening_type_id, $attribute_option_id)
    {
        $collection = $this->_openingTypesCollection->create();

        if(!empty($fixture_id)){
            $collection->addFieldToFilter('fixture_id', $fixture_id);
        }

        if(!empty($opening_type_id)){
            $collection->addFieldToFilter('opening_type_id', $opening_type_id);
        }

        if(!empty($attribute_option_id)){
            $collection->addFieldToFilter('attribute_option_id', $attribute_option_id);    
        }
        
        $collection->getFirstItem();

        return $collection;
    }
}

