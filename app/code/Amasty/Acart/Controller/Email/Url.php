<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Controller\Email;

use Amasty\Acart\Api\BlacklistRepositoryInterface;
use Amasty\Acart\Api\Data\BlacklistInterfaceFactory;
use Amasty\Acart\Api\RuleQuoteRepositoryInterface;
use Amasty\Acart\Controller\Email;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;

class Url extends Email
{
    /**
     * @var \Amasty\Acart\Model\App\Response\Redirect
     */
    private $redirect;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var RuleQuoteRepositoryInterface
     */
    private $ruleQuoteRepository;

    /**
     * @var BlacklistRepositoryInterface
     */
    protected $blacklistRepository;

    /**
     * @var BlacklistInterfaceFactory
     */
    protected $blacklistFactory;

    public function __construct(
        Context $context,
        \Amasty\Acart\Model\UrlManager $urlManager,
        \Amasty\Acart\Model\RuleQuote $ruleQuote,
        \Amasty\Acart\Model\ResourceModel\RuleQuote $ruleQuoteResource,
        \Amasty\Acart\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\SessionFactory $checkoutSessionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Amasty\Acart\Model\App\Response\Redirect $redirect,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        RuleQuoteRepositoryInterface $ruleQuoteRepository,
        BlacklistRepositoryInterface $blacklistRepository,
        BlacklistInterfaceFactory $blacklistFactory
    ) {
        parent::__construct(
            $context,
            $urlManager,
            $ruleQuote,
            $ruleQuoteResource,
            $historyCollectionFactory,
            $customerSession,
            $checkoutSessionFactory,
            $customerFactory,
            $quoteFactory
        );

        $this->redirect = $redirect;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->ruleQuoteRepository = $ruleQuoteRepository;
        $this->blacklistRepository = $blacklistRepository;
        $this->blacklistFactory = $blacklistFactory;
    }

    /**
     * @return \Amasty\Acart\Model\History|null
     */
    protected function getHistory()
    {
        $key = $this->getRequest()->getParam('key');

        /** @var \Amasty\Acart\Model\ResourceModel\History\Collection $historyResource */
        $historyResource = $this->historyCollectionFactory->create();
        $historyResource->addRuleQuoteData()
            ->addFieldToFilter('main_table.public_key', $key)
            ->setCurPage(1)
            ->setPageSize(1);

        $history = $historyResource->getFirstItem();

        if (!$history->getHistoryId() || $history->getPublicKey() != $key) {
            return null;
        }

        return $history;
    }

    /**
     * execute
     */
    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/redirecturl.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $url = $this->getRequest()->getParam('url');
        $mageUrl = $this->getRequest()->getParam('mageUrl');

        $history = $this->getHistory();

        if (!$history || (!$url && !$mageUrl)) {
            $this->_forward('defaultNoRoute');
            return null;
        }
        $this->urlManager->init($history->getRule(), $history);
        $target = null;

        if ($url) {
            // @codingStandardsIgnoreLine
            $target = $this->urlManager->getCleanUrl(base64_decode(urldecode($url)));
        } elseif ($mageUrl) {
            $target = $this->_url->getUrl(
                // @codingStandardsIgnoreLine
                $this->urlManager->getCleanUrl(base64_decode(urldecode($mageUrl))),
                $this->urlManager->getUtmParams()
            );
        }
        
        $this->loginCustomer($history);
        $ruleQuote = $this->ruleQuoteRepository->getById((int)$history->getRuleQuoteId());

        $ruleQuote->clickByLink($history);

        $ruleQuote->setAbandonedStatus(\Amasty\Acart\Model\RuleQuote::ABANDONED_RESTORED_STATUS);

        $this->ruleQuoteRepository->save($ruleQuote);
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        if($this->getRequest()->getParam('store_code') == "senseon_configurator_store_view"){
            $target = str_replace($storeManager->getStore(5)->getBaseUrl(), $storeManager->getStore(5)->getBaseUrl()."products/", $target);
            $target = str_replace("configurator/", "", $target);
        }else{
            $target = str_replace($storeManager->getStore(1)->getBaseUrl(), $storeManager->getStore(1)->getBaseUrl()."products/", $target);
        }
        return $this->_redirect($this->redirect->validateRedirectUrl($target));
    }

    /**
     * @param \Amasty\Acart\Model\History $history
     */
    protected function loginCustomer(\Amasty\Acart\Model\History $history)
    {
        /** @var Session $checkoutSession */
        $checkoutSession = $this->checkoutSessionFactory->create();

        if ($this->customerSession->isLoggedIn()) {
            if ($history->getCustomerId() != $this->customerSession->getCustomerId()) {
                $this->customerSession->logout();
            }
        }

        // customer. login
        if ($history->getCustomerId()) {
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->customerFactory->create()->load($history->getCustomerId());

            if ($customer->getId()) {
                $this->customerSession->setCustomerAsLoggedIn($customer);
                $this->flushSection($checkoutSession, 'customer');
            }
        } elseif ($history->getQuoteId()) {
            /**
             * visitor. restore quote in the session
             *
             * @var \Magento\Quote\Model\Quote $quote
             */
            $quote = $this->quoteFactory->create()->load($history->getQuoteId());

            if ($quote) {
                $checkoutSession->replaceQuote($quote);
                $quote->getBillingAddress()->setEmail($history->getEmail());
            }
        }

        if ($history->getSalesRuleCoupon()) {
            $code = $history->getSalesRuleCoupon();
            $quote = $checkoutSession->getQuote();

            if ($code && $quote) {
                $quote->setCouponCode($code)
                    ->collectTotals()
                    ->save();
            }
        }
    }

    /**
     * Change version for make section expired
     *
     * @param Session $checkoutSession
     * @param string $sectionName
     */
    private function flushSection(Session $checkoutSession, $sectionName)
    {
        if ($this->cookieManager->getCookie('mage-cache-sessid')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
        }

        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath($checkoutSession->getCookiePath());
        $sectionDataIds = json_decode($this->cookieManager->getCookie('section_data_ids', '{}'), true);
        $sectionDataIds[$sectionName] = isset($sectionDataIds[$sectionName]) ?
            $sectionDataIds[$sectionName] + 1000 :
            1000;
        $this->cookieManager->deleteCookie('section_data_ids');
        $this->cookieManager->setPublicCookie(
            'section_data_ids',
            json_encode($sectionDataIds),
            $metadata
        );
    }
}
