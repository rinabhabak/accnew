<?php
declare(strict_types=1);

namespace Int\DistributorGraphQl\Model\Resolver\FilterDistributorsByLocation;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Get identities from resolved data
 */
class Identity implements IdentityInterface
{
    //private $cacheTag = \Amasty\Storelocator\Model\Cache\Type::CACHE_TAG;

    private $cacheTag = \Amasty\Storelocator\Model\Location::CACHE_TAG;

    /**
     * Get identity tags from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        $items = $resolvedData[0]['items'] ?? [];
        foreach ($items as $item) {
            $ids[] = sprintf('%s_%s', $this->cacheTag, $item['id']);
        }
        if (!empty($ids)) {
            $ids[] = $this->cacheTag;
        }
        
        return $ids;
    }
}
