<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_AggregationGraphQl
 * @author    Indusnet
 */
declare(strict_types=1);

namespace Int\AggregationGraphQl\DataProvider\Product\LayeredNavigation\Builder;

use Magento\CatalogGraphQl\DataProvider\CategoryAttributesMapper;
use Magento\CatalogGraphQl\DataProvider\Category\Query\CategoryAttributeQuery;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilderInterface;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\RootCategoryProvider;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\AggregationValueInterface;
use Magento\Framework\Api\Search\BucketInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Formatter\LayerFormatter;
use Amasty\Shopby\Model\Source\VisibleInCategory;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;

/**
 * @inheritdoc
 */
class Category extends \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Category 
{
    /**
     * @var string
     */
    private const CATEGORY_BUCKET = 'category_bucket';

    /**
     * @var array
     */
    private static $bucketMap = [
        self::CATEGORY_BUCKET => [
            'request_name' => 'category_id',
            'label' => 'Category-local'
        ],
    ];

    /**
     * @var CategoryAttributeQuery
     */
    private $categoryAttributeQuery;

    /**
     * @var CategoryAttributesMapper
     */
    private $attributesMapper;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var RootCategoryProvider
     */
    private $rootCategoryProvider;

    /**
     * @var LayerFormatter
     */
    private $layerFormatter;

    private $settingHelper;

    private $category_setting;
    private $_filter;

    /**
     * @param CategoryAttributeQuery $categoryAttributeQuery
     * @param CategoryAttributesMapper $attributesMapper
     * @param RootCategoryProvider $rootCategoryProvider
     * @param ResourceConnection $resourceConnection
     * @param LayerFormatter $layerFormatter
     */
    public function __construct(
        CategoryAttributeQuery $categoryAttributeQuery,
        CategoryAttributesMapper $attributesMapper,
        RootCategoryProvider $rootCategoryProvider,
        ResourceConnection $resourceConnection,
        LayerFormatter $layerFormatter,
        \Amasty\Shopby\Helper\FilterSetting $settingHelper,
        Filter $filter
    ) {
        $this->categoryAttributeQuery = $categoryAttributeQuery;
        $this->attributesMapper = $attributesMapper;
        $this->resourceConnection = $resourceConnection;
        $this->rootCategoryProvider = $rootCategoryProvider;
        $this->layerFormatter = $layerFormatter;
        $this->settingHelper = $settingHelper;
        $this->_filter = $filter;
        parent::__construct($categoryAttributeQuery,$attributesMapper,$rootCategoryProvider,$resourceConnection,$layerFormatter);
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function build(AggregationInterface $aggregation, ?int $storeId): array
    {
        $this->category_setting = $this->settingHelper->getSettingByAttributeCode('category_ids');
        $this->checkFilterVisibility($this->category_setting,  $this->_filter);
       
        

        $bucket = $aggregation->getBucket(self::CATEGORY_BUCKET);
        if ($this->isBucketEmpty($bucket)) {
            return [];
        }

        $categoryIds = \array_map(
            function (AggregationValueInterface $value) {
                return (int)$value->getValue();
            },
            $bucket->getValues()
        );

       

        $categoryIds = \array_diff($categoryIds, [$this->rootCategoryProvider->getRootCategory($storeId)]);
        
        $categoryLabels = \array_column(
            $this->attributesMapper->getAttributesValues(
                $this->resourceConnection->getConnection()->fetchAll(
                    $this->categoryAttributeQuery->getQuery($categoryIds, ['name'], $storeId)
                )
            ),
            'name',
            'entity_id'
        );

        if (!$categoryLabels) {
            return [];
        }

        $result = $this->layerFormatter->buildLayer(
            self::$bucketMap[self::CATEGORY_BUCKET]['label'],
            \count($categoryIds),
            self::$bucketMap[self::CATEGORY_BUCKET]['request_name']
        );

        foreach ($bucket->getValues() as $value) {
            $categoryId = $value->getValue();
            if (!\in_array($categoryId, $categoryIds, true)) {
                continue ;
            }
            $result['options'][] = $this->layerFormatter->buildItem(
                $categoryLabels[$categoryId] ?? $categoryId,
                $categoryId,
                $value->getMetrics()['count']
            );
        }

        return [];
    }

    /**
     * Check that bucket contains data
     *
     * @param BucketInterface|null $bucket
     * @return bool
     */
    private function isBucketEmpty(?BucketInterface $bucket): bool
    {
        return null === $bucket || !$bucket->getValues();
    }

    protected function checkFilterVisibility($setting, $filter)
    {

        $visible = true;
        if ($setting->getVisibleInCategories() === VisibleInCategory::ONLY_IN_SELECTED_CATEGORIES
            && !in_array($currentCategoryId, $setting->getCategoriesFilter())
        ) {
            $visible = false;
        }

        if ($setting->getVisibleInCategories() === VisibleInCategory::HIDE_IN_SELECTED_CATEGORIES
            && in_array($currentCategoryId, $setting->getCategoriesFilter())
        ) {
            $visible = false;
        }

        return $visible;
    }
}
