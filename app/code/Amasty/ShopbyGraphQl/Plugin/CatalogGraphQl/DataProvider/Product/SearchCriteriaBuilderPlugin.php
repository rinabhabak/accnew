<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyGraphQl
 */


declare(strict_types=1);

namespace Amasty\ShopbyGraphQl\Plugin\CatalogGraphQl\DataProvider\Product;

use Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapper\RatingSummary;
use Amasty\Shopby\Model\Layer\Filter\Rating;
use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;

class SearchCriteriaBuilderPlugin
{
    /**
     * @param SearchCriteriaBuilder $subject
     * @param array $args
     * @param bool $includeAggregation
     * @return array
     */
    public function beforeBuild(SearchCriteriaBuilder $subject, $args, $includeAggregation)
    {
        if (isset($args['filter'][RatingSummary::FIELD_NAME])) {
            $args['filter'][RatingSummary::FIELD_NAME]['eq']
                = Rating::STARS[$args['filter'][RatingSummary::FIELD_NAME]['eq']] ?? 1;
        }

        return [$args, $includeAggregation];
    }
}
