<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_CustomerGraphQl
 * @author    Indusnet
 */

namespace Int\CustomerGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;


/**
 * Class PickYourIndustry
 * @package Int\CustomerGraphQl\Model\Resolver
 */
class PickYourIndustry implements ResolverInterface
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_entityAttribute;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection
     */
    protected $_entityAttributeOptionCollection;


     public function __construct(
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $entityAttributeOptionCollection
    ) {
        $this->_entityAttribute = $entityAttribute;
    $this->_entityAttributeOptionCollection = $entityAttributeOptionCollection;
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
            $customerCategory = [];

            $attributeInfo = $this->_entityAttribute->loadByCode('customer', 'pick_your_industry');
            $attribute_id = $attributeInfo->getAttributeId();
            $attributeOptionAll = $this->_entityAttributeOptionCollection
                    ->setPositionOrder('asc')
                    ->setAttributeFilter($attribute_id)
                    ->setStoreFilter()
                    ->load();
            $i = 0;
            foreach($attributeOptionAll as $option){
                $customerCategory[$i]['label'] = $option->getvalue();
            $customerCategory[$i]['value'] = $option->getoption_id();
            $i++;
            }
            



        return $customerCategory;

    }

}