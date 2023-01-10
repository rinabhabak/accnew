<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Model;

use Amasty\Acart\Model\Quote\Extension\Handlers\ReadHandler;

class Indexer
{
    const LAST_EXECUTED_CODE = 'amasty_acart_last_executed';
    const BATCH_SIZE = 200;

    /**
     * @var ResourceModel\Quote\CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var ResourceModel\History\CollectionFactory
     */
    private $historyCollectionFactory;

    /**
     * @var ResourceModel\RuleQuote\CollectionFactory
     */
    private $ruleQuoteCollectionFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $_dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $_date;

    /**
     * @var \Magento\Framework\FlagFactory
     */
    private $flagManagerFactory;

    /**
     * @var ResourceModel\RuleQuote
     */
    private $ruleQuoteResource;

    /**
     * @var RuleQuoteFromRuleAndQuoteFactory
     */
    private $ruleQuoteFromRuleAndQuoteFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\Flag
     */
    private $flagData;

    /**
     * @var int
     */
    protected $_actualGap = 600; //2 days

    /**
     * @var int|null
     */
    protected $_lastExecution = null;

    /**
     * @var int|null
     */
    protected $_currentExecution = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var ReadHandler
     */
    private $quoteReadHandler;

    public function __construct(
        \Amasty\Acart\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Amasty\Acart\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Amasty\Acart\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Amasty\Acart\Model\ResourceModel\RuleQuote\CollectionFactory $ruleQuoteCollectionFactory,
        \Amasty\Acart\Model\ConfigProvider $configProvider,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Amasty\Acart\Model\RuleQuoteFromRuleAndQuoteFactory $ruleQuoteFromRuleAndQuoteFactory,
        \Amasty\Acart\Model\ResourceModel\RuleQuote $ruleQuoteResource,
        \Magento\Framework\FlagFactory $flagManagerFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        ReadHandler $quoteReadHandler
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->ruleQuoteCollectionFactory = $ruleQuoteCollectionFactory;
        $this->ruleQuoteFromRuleAndQuoteFactory = $ruleQuoteFromRuleAndQuoteFactory;
        $this->_dateTime = $dateTime;
        $this->_date = $date;
        $this->timezoneInterface = $timezoneInterface;
        $this->configProvider = $configProvider;
        $this->ruleQuoteResource = $ruleQuoteResource;
        $this->flagManagerFactory = $flagManagerFactory;
        $this->resource = $resource;
        $this->logger = $logger;
        $this->quoteReadHandler = $quoteReadHandler;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function run()
    {
        $this->prepareRuleQuotes();
        $this->executeHistoryItems();
        $this->completeRuleQuotes();
        $this->getFlag()->save();
    }

    /**
     * @return void
     */
    protected function prepareRuleQuotes()
    {
        /** @var \Amasty\Acart\Model\ResourceModel\Quote\Collection $quoteToProcessCollection */
        $quoteToProcessCollection = $this->quoteCollectionFactory->create();
        $quoteToProcessCollection->addAbandonedCartsFilter()
            ->joinQuoteEmail(
                $this->configProvider->isDebugMode(),
                $this->configProvider->getDebugEnabledEmailDomains()
            );

        if (!$this->configProvider->isDebugMode()) {
            $quoteToProcessCollection->addTimeFilter(
                $this->_dateTime->formatDate($this->_getCurrentExecution() - $this->_actualGap),
                $this->_dateTime->formatDate($this->_getLastExecution() - $this->_actualGap)
            );
        }

        if ($this->configProvider->isOnlyCustomers()) {
            $quoteToProcessCollection->addFieldToFilter('main_table.customer_id', ['notnull' => true]);
        }

        /** @var \Amasty\Acart\Model\ResourceModel\Rule\Collection $activeRulesCollection */
        $activeRulesCollection = $this->ruleCollectionFactory->create()
            ->addFieldToFilter(Rule::IS_ACTIVE, Rule::RULE_ACTIVE)
            ->addOrder(Rule::PRIORITY, \Amasty\Acart\Model\ResourceModel\Quote\Collection::SORT_ORDER_ASC);
        $processedQuoteIds = [];

        foreach ($this->batchLoad($quoteToProcessCollection) as $quote) {
            foreach ($activeRulesCollection as $rule) {
                try {
                    if (!in_array($quote->getId(), $processedQuoteIds) && $rule->validate($quote)) {
                        $this->quoteReadHandler->read($quote);
                        $this->ruleQuoteFromRuleAndQuoteFactory->create($rule, $quote);
                        $processedQuoteIds[] = $quote->getId();
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    continue 2;
                }
            }
        }

        $this->deleteAmbiguousRuleQuotes();
    }

    private function batchLoad(\Magento\Framework\Data\Collection $collection, $batchSize = self::BATCH_SIZE)
    {
        $currentPage = 1;
        $collection->setPageSize($batchSize);
        $collection->setCurPage($currentPage);
        $totalPagesCount = $collection->getLastPageNumber();

        while ($currentPage <= $totalPagesCount) {
            $collection->clear();
            $collection->setCurPage($currentPage);

            foreach ($collection->getItems() as $item) {
                yield $item;
            }

            $currentPage++;
        }
    }

    /**
     * Delete previous rule_quote entities if a setting "send email one time per quote" is disabled.
     *
     * @return void
     */
    protected function deleteAmbiguousRuleQuotes()
    {
        $this->ruleQuoteResource->deleteNotUnique();
    }

    /**
     * _execute
     */
    protected function executeHistoryItems()
    {
        /** @var ResourceModel\History\Collection $historyCollection */
        $historyCollection = $this->historyCollectionFactory->create();
        $historyCollection->addRuleQuoteData()
            ->addRuleData()
            ->addTimeFilter(
                $this->_dateTime->formatDate($this->_getCurrentExecution()),
                $this->_dateTime->formatDate($this->_getLastExecution())
            )->addFieldToFilter('ruleQuote.' . RuleQuote::STATUS, RuleQuote::STATUS_PROCESSING);

        foreach ($this->batchLoad($historyCollection) as $history) {
            $history->execute();
        }
    }

    protected function completeRuleQuotes()
    {
        /** @var ResourceModel\RuleQuote\Collection $ruleQuoteCollection */
        $ruleQuoteCollection = $this->ruleQuoteCollectionFactory->create();
        $ruleQuoteCollection->addCompleteFilter();

        foreach ($this->batchLoad($ruleQuoteCollection) as $ruleQuote) {
            $ruleQuote->complete();
        }
    }

    /**
     * @return int
     */
    protected function _getLastExecution()
    {
        if ($this->_lastExecution === null) {
            $flag = $this->getFlag()->loadSelf();
            $this->_lastExecution = (string)$flag->getFlagData();

            if (empty($this->_lastExecution)) {
                $this->_lastExecution = $this->_date->gmtTimestamp() - $this->_actualGap;
            }

            $flag->setFlagData($this->_getCurrentExecution());
        }

        return $this->_lastExecution;
    }

    /**
     * @return \Magento\Framework\Flag
     */
    protected function getFlag()
    {
        if ($this->flagData === null) {
            $this->flagData = $this->flagManagerFactory->create(['data' => ['flag_code' => self::LAST_EXECUTED_CODE]]);
        }

        return $this->flagData;
    }

    /**
     * @return int
     */
    protected function _getCurrentExecution()
    {
        if ($this->_currentExecution === null) {
            $this->_currentExecution = $this->_date->gmtTimestamp();
        }

        return $this->_currentExecution;
    }
}
