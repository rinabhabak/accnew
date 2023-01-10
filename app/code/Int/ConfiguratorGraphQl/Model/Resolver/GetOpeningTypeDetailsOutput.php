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

class GetOpeningTypeDetailsOutput implements ResolverInterface
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

        if(empty($args['input']['opening_type_id'])){
            throw new GraphQlInputException(__('Opening Type is Required.'));
        }


        try{
            $opening_type_id = $args['input']['opening_type_id'];

            if(!empty($opening_type_id))
            {
                $model = $this->_openingTypesModel->create()->load($opening_type_id);

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
}

