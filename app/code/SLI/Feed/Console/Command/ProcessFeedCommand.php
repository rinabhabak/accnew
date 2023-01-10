<?php
/**
 * Copyright (c) 2015 S.L.I. Systems, Inc. (www.sli-systems.com) - All Rights Reserved
 * This file is part of Learning Search Connect.
 * Learning Search Connect is distributed under a limited and restricted
 * license – please visit www.sli-systems.com/LSC for full license details.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE. TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO
 * EVENT WILL SLI BE LIABLE TO YOU OR ANY OTHER PARTY FOR ANY GENERAL, DIRECT,
 * INDIRECT, SPECIAL, INCIDENTAL OR CONSEQUENTIAL LOSS OR DAMAGES OF ANY
 * CHARACTER ARISING OUT OF THE USE OF THE CODE AND/OR THE LICENSE INCLUDING
 * BUT NOT LIMITED TO PERSONAL INJURY, LOSS OF DATA, LOSS OF PROFITS, LOSS OF
 * ASSIGNMENTS, DATA OR OUTPUT FROM THE SERVICE BEING RENDERED INACCURATE,
 * FAILURE OF CODE, SERVER DOWN TIME, DAMAGES FOR LOSS OF GOODWILL, BUSINESS
 * INTERRUPTION, COMPUTER FAILURE OR MALFUNCTION, OR ANY AND ALL OTHER DAMAGES
 * OR LOSSES OF WHATEVER NATURE, EVEN IF SLI HAS BEEN INFORMED OF THE
 * POSSIBILITY OF SUCH DAMAGES.
 */

namespace SLI\Feed\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SLI\Feed\FeedManager;
use SLI\Feed\Logging\LoggerFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Process feed for one or more stores.
 *
 * @package SLI\Feed\Console\Command
 */
class ProcessFeedCommand extends Command
{
    /**
     * The feed manager
     *
     * @var FeedManager
     */
    protected $feedManager;

    /**
     * Logger factory
     *
     * @var LoggerFactoryInterface
     */
    protected $loggerFactory;

    /**
     * @var State
     */
    protected $state;

    /**
     * @param FeedManager $feedManager
     * @param LoggerFactoryInterface $loggerFactory
     * @param State $state
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(FeedManager\Proxy $feedManager, LoggerFactoryInterface $loggerFactory, State $state)
    {
        parent::__construct();

        $this->feedManager = $feedManager;
        $this->loggerFactory = $loggerFactory;
        $this->state = $state;

    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sli:feed:process')
            ->setDescription('Generate and FTP a feed via SLI Learning Search Connect')
            ->addOption('skip-ftp', null, InputOption::VALUE_NONE, 'Skip FTP and only run feed generation.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force feed generation even if locked.')
            ->addArgument('storeId', InputArgument::OPTIONAL, 'Store id.', 'all');
        parent::configure();
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws LocalizedException $e
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // prepare context to be able to run product generator
        $verbose = $input->getOption('verbose');

        // enable verbose mode if one of the store log level is debug
        if (Logger::DEBUG == $this->feedManager->getLogLevelForCLI()) {
            $verbose = true;
        }

        if (!$input->getOption('quiet')) {
            // add (almost) output handler
            $handler = new StreamHandler('php://stdout', $verbose ? Logger::DEBUG : Logger::INFO);
            $this->loggerFactory->addLoggerHandler($handler, LoggerFactoryInterface::STORE_LOGGER);
            $this->loggerFactory->addLoggerHandler($handler, LoggerFactoryInterface::GENERAL_LOGGER);
        }

        $logger = $this->loggerFactory->getGeneralLogger();
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
            $areaCode = $this->state->getAreaCode();
            if ($areaCode === Area::AREA_ADMINHTML) {
                $logger->warning(sprintf(
                    'Area code was already set to \'%1$s\' while LSC is trying to set it to \'%1$s\'. '
                    . 'Continuing with the feed generation as the area code is already set to what we expect.',
                    Area::AREA_ADMINHTML
                ));
            } else {
                $logger->error(sprintf(
                    'Area code was set to \'%s\' but LSC is trying to set it to \'%s\'. '
                    . 'Terminating the feed generation as there is a conflict between two different area codes.',
                    $areaCode,
                    Area::AREA_ADMINHTML
                ));
                throw new \Exception($e);
            }
        }

        $storeId = $input->getArgument('storeId');
        // we can only disable ftp or fallback to configuration here
        $ftp = $input->getOption('skip-ftp') ? false : null;
        $force = $input->getOption('force');
        $results = [];

        if ('all' == $storeId) {
            if ($verbose) {
                $output->writeln('Starting product feed generation for all stores');
            }
            $results = $this->feedManager->processAllStores($ftp, $force);
        } else {
            if ($verbose) {
                $output->writeln('Starting product feed generation for storeId: ' . $storeId);
            }
            $results[$storeId]['status'] = $this->feedManager->processFeedForStoreId($storeId, $ftp, $force);
        }

        if ($verbose) {
            foreach ($results as $storeId => $meta) {
                $output->writeln('StoreId: ' . $storeId . ' - ' . $meta['status']);
            }
        }

        return 0;
    }
}
