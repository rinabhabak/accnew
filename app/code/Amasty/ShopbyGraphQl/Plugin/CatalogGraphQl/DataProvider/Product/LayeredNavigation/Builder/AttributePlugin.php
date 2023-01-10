<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyGraphQl
 */


declare(strict_types=1);

namespace Amasty\ShopbyGraphQl\Plugin\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder;

use Amasty\Shopby\Helper\FilterSetting;
use Amasty\ShopbyBase\Helper\OptionSetting;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Attribute;
use Magento\Framework\Api\Search\AggregationInterface;
use Psr\Log\LoggerInterface;

class AttributePlugin
{
    const RATING_SUMMARY_BUCKET = 'rating_summary_bucket';
    const RATING_STARS = 5;

    /**
     * @var OptionSetting
     */
    private $optionSetting;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        OptionSetting $optionSetting,
        LoggerInterface $logger
    ) {
        $this->optionSetting = $optionSetting;
        $this->logger = $logger;
    }

    /**
     * @param Attribute $subject
     * @param array $result
     * @param AggregationInterface $aggregation
     * @param int $storeId
     * @return array
     */
    public function afterBuild(Attribute $subject, $result, AggregationInterface $aggregation, $storeId): array
    {
        foreach ($result as &$filter) {
            foreach ($filter['options'] ?? [] as $key => $option) {
                $filter['options'][$key] += $this->getOptionData($option, $filter, $storeId);
            }
        }

        if (isset($result[self::RATING_SUMMARY_BUCKET])) {
           $this->prepareRatingFilter($result[self::RATING_SUMMARY_BUCKET]);
        }

        return $result;
    }

    /**
     * @param array $option
     * @param array $filter
     * @param int $storeId
     * @return array
     */
    private function getOptionData($option, $filter, $storeId)
    {
        try {
            $optionSetting = $this->optionSetting->getSettingByValue(
                $option['value'],
                FilterSetting::ATTR_PREFIX . $filter['attribute_code'],
                $storeId
            );
            $data = $optionSetting->getData();
        } catch (\Exception $e) {
            $data = [];
            $this->logger->error($e->getMessage());
        }

        return $data;
    }

    /**
     * @param array $ratingFilter
     */
    private function prepareRatingFilter(&$ratingFilter)
    {
        $allCount = 0;
        $listData = [];
        for ($key = self::RATING_STARS; $key >= 1; $key--) {
            foreach ($ratingFilter['options'] as $option) {
                if ($option['value'] == $key) {
                    $allCount += $option['count'];

                    $listData[] = [
                        'label' => $option['label'],
                        'value' => $key,
                        'count' => $allCount
                    ];
                    continue 2;
                }
            }

        }

        if ($listData) {
            $ratingFilter['options'] = $listData;
        }
    }
}
