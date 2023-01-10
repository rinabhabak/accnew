<?php
namespace Int\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor;

use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Class CatalogCategoryProductJoinProcessor
 */
class CatalogCategoryProductJoinProcessor implements CollectionProcessorInterface
{
    /**
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $attributeNames
     * @return Collection
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames,
        $context = NULL
    ): Collection {
        if ($categoryFilter = $this->getFilterByFieldName($searchCriteria, 'category_id')) {
            try {
                $collection->joinField(
                    'position',
                    'catalog_category_product',
                    'position',
                    'product_id=entity_id',
                    '{{table}}.category_id IN ('. $categoryFilter->getValue() .')',
                    'inner'
                );
            } catch (LocalizedException $e) {
                // join already exists
                return $collection;
            }
        }

        return $collection;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param $fieldName
     * @return Filter|null
     */
    private function getFilterByFieldName(SearchCriteriaInterface $searchCriteria, $fieldName)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            /** @var Filter $filter */
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === $fieldName) {
                    return $filter;
                }
            }
        }

        return null;
    }
}
