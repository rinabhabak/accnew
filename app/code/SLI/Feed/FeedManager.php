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

namespace SLI\Feed;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Monolog\Logger;
use SLI\Feed\Helper\FTPUpload;
use SLI\Feed\Helper\GeneratorHelper;
use SLI\Feed\Logging\LoggerFactoryInterface;
use SLI\Feed\Model\GenerateFlag;

/**
 * Feed manager
 *
 * Coordinates locking, feed generation and FTP upload.
 *
 * @package SLI\Feed
 */
class FeedManager
{
    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED = 'failed';
    const STATUS_DISABLED = 'disabled';
    const STATUS_LOCKED = 'locked';

    /**
     * Feed generator.
     *
     * @var FeedGenerator
     */
    protected $feedGenerator;

    /**
     * FTP upload.
     *
     * @var FTPUpload
     */
    protected $ftpUpload;

    /**
     * Logger factory
     *
     * @var LoggerFactoryInterface
     */
    protected $loggerFactory;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Helper
     *
     * @var GeneratorHelper
     */
    protected $generatorHelper;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * Flag to manage feed generation state
     *
     * @var GenerateFlag
     */
    protected $generateFlag;

    /**
     * @param FeedGenerator $feedGenerator
     * @param FTPUpload $ftpUpload
     * @param LoggerFactoryInterface $loggerFactory
     * @param StoreManagerInterface $storeManager
     * @param GeneratorHelper $generatorHelper
     * @param TimezoneInterface $localeDate
     * @param GenerateFlag $generateFlag
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        FeedGenerator $feedGenerator,
        FTPUpload $ftpUpload,
        LoggerFactoryInterface $loggerFactory,
        StoreManagerInterface $storeManager,
        GeneratorHelper $generatorHelper,
        TimezoneInterface\Proxy $localeDate,
        GenerateFlag $generateFlag
    ) {
        $this->feedGenerator = $feedGenerator;
        $this->ftpUpload = $ftpUpload;
        $this->loggerFactory = $loggerFactory;
        $this->storeManager = $storeManager;
        $this->generatorHelper = $generatorHelper;
        $this->localeDate = $localeDate;
        $this->generateFlag = $generateFlag;
    }

    /**
     * Get the generate flag.
     *
     * @return GenerateFlag
     */
    public function getGenerateFlag()
    {
        return $this->generateFlag->loadSelf();
    }

    /**
     * Run everything required for a store feed includes FTP (unless disabled).
     *
     * @param int $storeId
     * @param bool|null $ftp Enabled or disabled ftp, Null to get the settings from UI
     * @param bool|false $force
     * @return string Result of storeId.
     */
    public function processFeedForStoreId($storeId, $ftp = null, $force = false)
    {
        $logger = $this->loggerFactory->getGeneralLogger();

        if (!$this->isValidStoreId($storeId)) {
            $logger->error(sprintf('storeId [%s] does not exist', $storeId));

            return static::STATUS_FAILED;
        }

        // test before starting at all
        if (!$this->acquireLock($force)) {
            return static::STATUS_LOCKED;
        }

        // init flag data for UI
        $flagData = [
            'has_errors' => false,
            'timeout_reached' => false,
            'message' => '',
        ];
        $this->generateFlag->setFlagData($flagData)->save();

        $result = $this->doProcessForStoreId($storeId, $ftp);

        // prepare data for UI
        $results = [];
        $results[$storeId]['name'] = $this->storeManager->getStore($storeId)->getName();
        $results[$storeId]['status'] = $result;

        $this->generateFlag->release($results, $this->localeDate->date());

        return $result;
    }

    /**
     * Process feed for all stores
     *
     * @param bool|null $ftp Enabled or disabled ftp, Null to get the settings from UI
     * @param bool|false $force Force generation even if locked.
     * @return array Map of storeId => result.
     */
    public function processAllStores($ftp = null, $force = false)
    {
        $results = [];

        // test before starting at all
        if (!$this->acquireLock($force)) {
            $results['all']['status'] = static::STATUS_LOCKED;
            return $results;
        }

        // init flag data for UI
        $flagData = [
            'has_errors' => false,
            'timeout_reached' => false,
            'message' => '',
        ];
        $this->generateFlag->setFlagData($flagData)->save();

        foreach ($this->storeManager->getStores() as $store) {
            $storeId = $store->getStoreId();
            $results[$storeId]['name'] = $store->getName();
            $results[$storeId]['status'] = $this->doProcessForStoreId($storeId, $ftp);
        }

        $this->generateFlag->release($results, $this->localeDate->date());

        return $results;
    }

    /**
     * @param $storeId
     * @param bool $ftp
     * @return string
     */
    protected function doProcessForStoreId($storeId, $ftp)
    {
        $logger = $this->loggerFactory->getGeneralLogger();

        if (!$this->generatorHelper->isAllowed($storeId)) {
            $logger->warning(sprintf('Feed generation disabled for storeId [%s]', $storeId));

            return static::STATUS_DISABLED;
        }

        try {
            $feedFilename = sprintf($this->generatorHelper->getFeedFileTemplate(), $storeId);
            if (!$this->feedGenerator->generateForStoreId($storeId, $feedFilename)) {
                $logger->error(sprintf('[%s] Feed generation failed', $storeId));

                return static::STATUS_FAILED;
            }

            // get ftp enabled/disabled settings from UI or from command line ($ftp)
            if ((null === $ftp && $this->ftpUpload->isAllowed($storeId)) || $ftp) {
                if (!$this->ftpUpload->writeFileToFTP($feedFilename, $storeId)) {
                    $logger->error(sprintf('[%s] FTP failed', $storeId));

                    return static::STATUS_FAILED;
                }
            }
        } catch (\Exception $e) {
            $logger->error(sprintf('[%s] Feed process failed: %s', $storeId, $e->getMessage()), ['exception' => $e]);
            $this->generateFlag->setError($e);

            return static::STATUS_FAILED;
        }

        return static::STATUS_SUCCESSFUL;
    }

    /**
     * Acquire lock.
     *
     * @param bool|false $force
     * @return bool true if lock was available and is now locked.
     */
    protected function acquireLock($force = false)
    {
        $logger = $this->loggerFactory->getGeneralLogger();

        // test before starting at all
        if ($this->generateFlag->loadSelf()->isLocked()) {
            if ($force) {
                $logger->warning('Force terminating any running feed generation');
                $this->generateFlag->release();
            } else {
                $logger->warning('Another feed generation has already started');

                return false;
            }
        }

        // lock
        $this->generateFlag->lock();

        return true;
    }

    /**
     * Check is store Id valid
     *
     * @param int $storeId
     * @return bool
     */
    protected function isValidStoreId($storeId)
    {
        foreach ($this->storeManager->getStores() as $store) {
            if ($store->getStoreId() == $storeId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get log level for CLI, DEBUG if one of the store log level is debug, otherwise INFO
     *
     * @return mixed
     */
    public function getLogLevelForCLI()
    {
        foreach ($this->storeManager->getStores() as $store) {
            if ('debug' == $this->generatorHelper->getLogLevel($store->getStoreId())) {
                return Logger::DEBUG;
            }
        }

        return Logger::INFO;
    }
}
