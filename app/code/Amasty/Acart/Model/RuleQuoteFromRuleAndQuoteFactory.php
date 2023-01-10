<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Model;

use Amasty\Acart\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Amasty\Acart\Api\RuleQuoteRepositoryInterface;
use Magento\Framework\Stdlib;

class RuleQuoteFromRuleAndQuoteFactory
{
    /**
     * @var Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var RuleQuoteFactory
     */
    private $ruleQuoteFactory;

    /**
     * @var RuleQuoteRepositoryInterface
     */
    private $ruleQuoteRepository;

    /**
     * @var HistoryFromRuleQuoteFactory
     */
    private $historyFromRuleQuoteFactory;

    /**
     * @var ScheduleCollectionFactory
     */
    private $scheduleCollectionFactory;

    public function __construct(
        Stdlib\DateTime\DateTime $date,
        Stdlib\DateTime $dateTime,
        RuleQuoteFactory $ruleQuoteFactory,
        RuleQuoteRepositoryInterface $ruleQuoteRepository,
        \Amasty\Acart\Model\HistoryFromRuleQuoteFactory $historyFromRuleQuoteFactory,
        ScheduleCollectionFactory $scheduleCollectionFactory
    ) {
        $this->date = $date;
        $this->dateTime = $dateTime;
        $this->ruleQuoteFactory = $ruleQuoteFactory;
        $this->ruleQuoteRepository = $ruleQuoteRepository;
        $this->historyFromRuleQuoteFactory = $historyFromRuleQuoteFactory;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
    }

    /**
     * @param Rule $rule
     * @param \Magento\Quote\Model\Quote $quote
     * @param bool $testMode
     *
     * @return RuleQuote
     */
    public function create(
        \Amasty\Acart\Model\Rule $rule,
        \Magento\Quote\Model\Quote $quote,
        $testMode = false
    ) {
        /** @var RuleQuote $ruleQuote */
        $ruleQuote = $this->ruleQuoteFactory->create();
        $customerEmail = $quote->getCustomerEmail();
        if (!$customerEmail
            && $quote->getExtensionAttributes()
            && $quote->getExtensionAttributes()->getAmAcartQuoteEmail()
            && $quote->getExtensionAttributes()->getAmAcartQuoteEmail()->getCustomerEmail()
        ) {
            $customerEmail = $quote->getExtensionAttributes()->getAmAcartQuoteEmail()->getCustomerEmail();
        }

        if ($customerEmail) {
            $time = $this->date->gmtTimestamp();
            $ruleQuote->setData(
                [
                    'rule_id' => $rule->getRuleId(),
                    'quote_id' => $quote->getId(),
                    'store_id' => $quote->getStoreId(),
                    'status' => RuleQuote::STATUS_PROCESSING,
                    'customer_id' => $quote->getCustomerId(),
                    'customer_email' => $customerEmail,
                    'customer_firstname' => $quote->getCustomerFirstname(),
                    'customer_lastname' => $quote->getCustomerLastname(),
                    'test_mode' => $testMode,
                    'created_at' => $this->dateTime->formatDate($time)
                ]
            );

            $this->ruleQuoteRepository->save($ruleQuote);
            $histories = [];

            /** @var \Amasty\Acart\Model\ResourceModel\Schedule\Collection $scheduleCollection */
            $scheduleCollection = $this->scheduleCollectionFactory->create();
            $scheduleCollection->addFieldToFilter(Schedule::RULE_ID, $rule->getRuleId());

            foreach ($scheduleCollection as $schedule) {
                $histories[] = $this->historyFromRuleQuoteFactory->create($ruleQuote, $schedule, $rule, $quote, $time);
            }

            if (!$histories) {
                throw new \Magento\Framework\Exception\LocalizedException(__("Rule do not have any Schedule"));
            }

            $ruleQuote->setData('assigned_history', $histories);
        }

        return $ruleQuote;
    }
}
