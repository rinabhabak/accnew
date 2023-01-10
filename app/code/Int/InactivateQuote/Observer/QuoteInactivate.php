<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_InactivateQuote
 * @author    Indusnet
 */
namespace Int\InactivateQuote\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class QuoteInactivate
 * @package Int\InactivateQuote\Observer
 */
class QuoteInactivate implements ObserverInterface
{
    /**
    * @var LoggerInterface
    */
    protected $logger;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepositary;

    /**
     * OrderCancellation constructor.
     * @param CartRepositoryInterface $quoteRepositary
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartRepositoryInterface $quoteRepositary,
        LoggerInterface $logger
    ) {
        $this->quoteRepositary = $quoteRepositary;
        $this->logger = $logger;
    }//end __construct()


    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        if ($order->getQuoteId()) {
            try {
                $this->setQuoteInactive($order->getQuoteId());
            } catch (\Exception $ex) {
                $this->logger->error($ex->getMessage());
            }
        }
    }


    /**
     * @param $quoteId
     */
    public function setQuoteInactive($quoteId)
    {
        try {
            $quote = $this->quoteRepositary->get($quoteId);
            if ($quote->getIsActive()) {
                $this->logger->warning("Duplicate quote id " . $quoteId . " found.");
                $quote->setIsActive(false);
                $this->quoteRepositary->save($quote);
            }
        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage());
        }
    }
}
