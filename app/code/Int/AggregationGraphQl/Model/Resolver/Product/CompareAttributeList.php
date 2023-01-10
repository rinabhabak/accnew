<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_AggregationGraphQl
 * @author    Indusnet
 */

namespace Int\AggregationGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class CompareAttributeList
 * @package Int\AggregationGraphQl\Model\Resolver
 */
class CompareAttributeList implements ResolverInterface
{
    /**
     * @inheritdoc
     */


    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
    }
 


    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        
        $product = $value['model'];
        $entity_id = $value['entity_id'];
        $_product = $this->productRepository->getById($product->getEntityId());
        $attributes = $_product->getAttributes();
        
        $attributeSearchCriteria = $this->searchCriteriaBuilder->create();
       $attributeRepository = $this->attributeRepository->getList(
                'catalog_product',
                $attributeSearchCriteria
            );
            $additionalAttributeCollection = [];
            $i = 0;

            foreach ($attributes as $attribute) { 
                if ($attribute->getIsComparable()) {
            $additionalAttributeCollection[$i]['attribute_name'] = $attribute->getAttributeCode();
            $additionalAttributeCollection[$i]['frontend_label'] = $attribute->getFrontendLabel();
            if($attribute->getFrontend()->getValue($_product))
                $attribute_value = $attribute->getFrontend()->getValue($_product);
            else
                $attribute_value = null;
            $additionalAttributeCollection[$i]['attribute_value'] =$attribute_value;
             $i++;
            }
        }
        return $additionalAttributeCollection;

    }

}