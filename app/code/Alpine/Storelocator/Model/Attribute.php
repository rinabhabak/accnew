<?php
/**
 * Alpine_Storelocator
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\Storelocator\Model;

use Amasty\Storelocator\Model\ResourceModel\Attribute\CollectionFactory;

/**
 * Alpine\Storelocator\Model\Attribute
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 */
class Attribute
{
    /**
     * Industry attribute code
     *
     * @var string
     */
    const INDUSTRY_CODE = 'industry';
    
    /**
     * Attribute collection factory
     *
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;
    
    /**
     * Constructor
     *
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }
    
    /**
     * Get attribute id by code
     *
     * @param string $code
     * @return int
     */
    public function getAttributeIdByCode($code)
    {
        return $this->attributeCollectionFactory->create()
            ->addFieldToFilter('attribute_code', $code)
            ->getFirstItem()
            ->getId();
    }
}
