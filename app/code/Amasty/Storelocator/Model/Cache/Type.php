<?php
    namespace Amasty\Storelocator\Model\Cache;

    use Magento\Framework\App\Cache\Type\FrontendPool;
    use Magento\Framework\Cache\Frontend\Decorator\TagScope;

    /**
     * System / Cache Management / Cache type "Your Cache Type Label"
     * For store locator
     */
    class Type extends TagScope
    {
        /**
         * Cache type code unique among all cache types
         */
        const TYPE_IDENTIFIER = 'amasty_storelocator';

        /**
         * The tag name that limits the cache cleaning scope within a particular tag
         */
        const CACHE_TAG = 'AMASTY_STORELOCATOR';

        /**
         * @param FrontendPool $cacheFrontendPool
         */
        public function __construct(FrontendPool $cacheFrontendPool)
        {
            parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
        }
    }
