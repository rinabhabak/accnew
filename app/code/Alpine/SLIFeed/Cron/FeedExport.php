<?php
/**
 * Cron execution class
 * 
 * @category    Alpine
 * @package     Alpine_SLIFeed
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Evgeniy Derevyanko <evgeniy.derevyanko@alpineinc.com>
 */

namespace Alpine\SLIFeed\Cron;

use SLI\Feed\FeedManager;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;

/**
 * Cron execution class
 * 
 * @category    Alpine
 * @package     Alpine_SLIFeed
 */
class FeedExport 
{
    /**
     * The feed manager
     *
     * @var FeedManager
     */
    protected $feedManager;

    /**
     * Logger interface
     *
     * @var LoggerInterface
     */
    protected $logger;    
    
    /**
     * Config interface
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;    
    
    /**
     * Feed export class constructor
     * 
     * @param FeedManager $feedManager
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        FeedManager $feedManager, 
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->feedManager = $feedManager;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * cron execute method
     */
    public function execute() 
    {
        $enabled = $this->scopeConfig->getValue(
            'alpine_feed_export/general/sli_feed_cron_setting_enable', 
            ScopeInterface::SCOPE_STORE
        );
        
        if ($enabled) {
            $this->logger->info('Start SLI feed export');
            $results = $this->feedManager->processAllStores();
        }
    }
}
