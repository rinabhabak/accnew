<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\AttributeSetGraphQl\Model\Resolver\DataProvider;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollection;
use Magento\Catalog\Model\Product\Attribute\Repository as ProductAttributeRepository;

class GetAttributeList
{
    /**
     * @var ProductAttributeRepository
     */
    protected $productAttributeRepository;

    /**
     * @var ProductAttributeCollection
     */
    protected $productAttributeCollection;

    /**
     * @param ProductAttributeRepository $productAttributeRepository
     * @param ProductAttributeCollection $productAttributeCollection
     */
    public function __construct(
        ProductAttributeRepository $productAttributeRepository,
        ProductAttributeCollection $productAttributeCollection
    ){
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productAttributeCollection = $productAttributeCollection;
    }

    public function getGetAttributeList($attribute_code = null)
    {
        $groupId = 125;
        $attributes = [];

        $groupAttributesCollection = $this->productAttributeCollection->create()
                //->setAttributeGroupFilter($groupId)
                ->addVisibleFilter()
                ->load();

        foreach ($groupAttributesCollection->getItems() as $key => $attribute)
        {
            if(!$attribute->getAttributeId()){
                continue;
            }

            if(!empty($attribute_code) && $attribute_code !== $attribute->getAttributeCode()){
                continue;
            }

            $attributes[$key]['attribute_id'] = $attribute->getAttributeId();
            $attributes[$key]['attribute_code'] = $attribute->getAttributeCode();
            $attributes[$key]['attribute_label'] = $attribute->getStoreLabel();
            $attributes[$key]['attribute_options'] = $this->getAttributeOptions($attribute->getAttributeCode());
            $attributes[$key]['position'] = $attribute->getPosition();
        }

        return (array) $attributes;
    }

    protected function getAttributeOptions($attribute_code = NULL)
    {
        $options = [];

        if(empty($attribute_code) && is_null($attribute_code)){
            return $options;
        }

        $attributeOptions = $this->productAttributeRepository->get($attribute_code)->getOptions();

        foreach ($attributeOptions as $key => $attributeOption){
        
            if(!empty($attributeOption->getLabel()) && !empty($attributeOption->getValue())){
                $options[$key]['option_label'] = $attributeOption->getLabel();
                $options[$key]['option_value'] = $attributeOption->getValue();
            }
        }
        
        return $options;
    }
}

