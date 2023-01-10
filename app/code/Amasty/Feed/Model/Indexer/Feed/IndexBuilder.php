<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Indexer\Feed;

use Amasty\Feed\Model\Indexer\Product\ProductFeedProcessor;
use \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;
use Amasty\Feed\Model\Feed;
use Magento\Framework\Indexer\StateInterface;

/**
 * Class IndexBuilder
 */
class IndexBuilder extends \Amasty\Feed\Model\Indexer\AbstractIndexBuilder
{
    /**
     * @var string
     */
    private $productIndexState;

    /**
     * @var string
     */
    private $ruleIndexState;

    /**
     * Reindex by id
     *
     * @param int $feedId
     *
     * @return void
     * @api
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reindexByFeedId($feedId)
    {
        $this->reindexByFeedIds([$feedId]);
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexByFeedIds(array $ids)
    {
        try {
            $this->doReindexByFeedIds($ids);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    public function lockReindex()
    {
        $state = $this->stateFactory->create()
            ->loadByIndexer(ProductFeedProcessor::INDEXER_ID);
        $this->productIndexState = $state->getStatus();
        $state->setStatus(StateInterface::STATUS_WORKING)
            ->save();

        $state = $this->stateFactory->create()
            ->loadByIndexer(FeedRuleProcessor::INDEXER_ID);
        $this->ruleIndexState = $state->getStatus();
        $state->setStatus(StateInterface::STATUS_WORKING)
            ->save();
    }

    public function unlockReindex()
    {
        $this->stateFactory->create()
            ->loadByIndexer(ProductFeedProcessor::INDEXER_ID)
            ->setStatus($this->productIndexState)
            ->save();

        $this->stateFactory->create()
            ->loadByIndexer(FeedRuleProcessor::INDEXER_ID)
            ->setStatus($this->ruleIndexState)
            ->save();
    }

    /**
     * Reindex by ids. Template method
     *
     * @param array $ids
     *
     * @return void
     * @throws \Exception
     */
    protected function doReindexByFeedIds($ids)
    {
        $this->deleteByFeedIds($ids);
        /** @var \Amasty\Feed\Model\ResourceModel\Feed\Collection $collection */
        $collection = $this->getAllFeeds()->addFieldToFilter('entity_id', ['in' => $ids]);

        /** @var \Amasty\Feed\Model\Feed $feed */
        foreach ($collection->getItems() as $feed) {
            $this->processFeed($feed);
        }
    }

    /**
     * Full reindex Template method
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function doReindexFull()
    {
        $this->truncateTable();

        /** @var \Amasty\Feed\Model\Feed $feed */
        foreach ($this->getAllFeeds() as $feed) {
            $this->processFeed($feed);
        }
    }
}
