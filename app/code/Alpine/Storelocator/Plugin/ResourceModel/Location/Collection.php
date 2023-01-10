<?php
/**
 * Alpine_Storelocator
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Evgeniy Derevyanko <evgeniy.derevyanko@alpineinc.com>
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */

namespace Alpine\Storelocator\Plugin\ResourceModel\Location;

use Amasty\Storelocator\Block\LocationFactory;
use Alpine\Storelocator\Model\Attribute;
use Alpine\Storelocator\Plugin\Block\Location as LocationPlugin;

/**
 * Alpine\Storelocator\Plugin\ResourceModel\Location
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 */
class Collection
{
    /**
     * option value for ALL industries
     * 
     * @var string
     */
    const CONST_KEY_ALL = 'All';

    /**
     * Location block factory
     * 
     * @var LocationFactory
     */
    protected $locationBlock;

    /**
     * Attribute model
     *
     * @var Attribute
     */
    protected $attributeModel;

    /**
     * Init method
     * 
     * @param LocationFactory $locationFactory
     * @param Attribute $attributeModel
     */
    public function __construct(
        LocationFactory $locationFactory,
        Attribute $attributeModel
    ) {
        $this->locationBlock = $locationFactory->create();
        $this->attributeModel = $attributeModel;
    }

    /**
     * Around plugin for applyAttributeFilters method
     * 
     * @var Amasty\Storelocator\Model\ResourceModel\Location\Collection $subject
     * @var callable $procede
     * @var array $data
     * @return Amasty\Storelocator\Model\ResourceModel\Location\Collection
     */
    public function aroundApplyAttributeFilters($subject, $procede, $data) {
        $attributeId = 0;
        $optionAllId = 0;
        $attributes = $this->locationBlock->getAttributes();

        $attributeId = $this->attributeModel->getAttributeIdByCode(LocationPlugin::CONST_INDUSTRY_CODE);

        foreach ($attributes as $attribute) {
            if ($attribute['attribute_id'] == $attributeId) {
                foreach ($attribute['options'] as $key => $option) {
                    if ($option == self::CONST_KEY_ALL) {
                        $optionAllId = $key;
                        break;
                    }
                }
                break;
            }
        }

        if ($attributeId
            && $optionAllId
            && isset($data[$attributeId])
            && $data[$attributeId] == $optionAllId
        ) {
            unset($data[$attributeId]);
        }

        return $procede($data);     
    }
}
