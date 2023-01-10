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

namespace SLI\Feed\Logging;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SLI\Feed\Helper\GeneratorHelper;

/**
 * Class LoggerFactory
 *
 * @package SLI\Feed\Logging
 */
class LoggerFactory implements LoggerFactoryInterface
{
    protected $logfileTemplates;
    protected $loggers;
    protected $logLevel;
    protected $logFilePath;
    protected $filesystem;
    protected $generatorHelper;
    protected $additionalHandlers;

    /**
     * @param array $logfileTemplates
     * @param string $logLevel
     * @param string $logFilePath
     * @param Filesystem $filesystem
     * @param GeneratorHelper $generatorHelper
     */
    public function __construct(array $logfileTemplates, $logLevel, $logFilePath, Filesystem $filesystem, GeneratorHelper $generatorHelper)
    {
        $this->logfileTemplates = $logfileTemplates;
        $this->loggers = [];
        $this->logLevel = $logLevel;
        $this->logFilePath = $logFilePath;
        $this->filesystem = $filesystem;
        $this->generatorHelper = $generatorHelper;
        $this->additionalHandlers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getGeneralLogger()
    {
        $logfile = $this->getLogfileTemplateForType('general');

        return $this->getLoggerForLogfile($logfile, static::GENERAL_LOGGER);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreLogger($storeId, $prefix = '')
    {
        $logfile = sprintf($this->getLogfileTemplateForType('store'), $prefix, $storeId);

        return $this->getLoggerForLogfile($logfile, static::STORE_LOGGER, $this->generatorHelper->getLogLevel($storeId));
    }

    /**
     * {@inheritdoc}
     */
    public function addLoggerHandler(HandlerInterface $handler, $type)
    {
        if (!array_key_exists($type, $this->additionalHandlers)) {
            $this->additionalHandlers[$type] = [];
        }

        $this->additionalHandlers[$type][] = $handler;
    }

    /**
     * Get a logfilename template for the given type.
     *
     * @param string $type
     * @return string
     */
    protected function getLogfileTemplateForType($type)
    {
        if (!array_key_exists($type, $this->logfileTemplates)) {
            throw new \InvalidArgumentException(sprintf('Unsupported logger type: %s', $type));
        }

        return $this->getLogPath() . $this->logfileTemplates[$type];
    }

    /**
     * Get a logger for the given logfile.
     *
     * @param string $logfile
     * @param string $type
     * @param string|null $logLevel
     * @return Logger
     */
    protected function getLoggerForLogfile($logfile, $type, $logLevel = null)
    {
        if (!array_key_exists($logfile, $this->loggers)) {
            $logLevel = $logLevel ?: $this->logLevel;
            $llMap = [
                'error' => Logger::INFO,
                'debug' => Logger::DEBUG,
            ];
            if (array_key_exists($logLevel, $llMap)) {
                $logLevel = $llMap[$logLevel];
            }

            $logger = new Logger($type);
            $logger->pushHandler(new StreamHandler($logfile, $logLevel));

            // add additional handler
            if (array_key_exists($type, $this->additionalHandlers)) {
                foreach ($this->additionalHandlers[$type] as $handler) {
                    $logger->pushHandler($handler);
                }
            }

            $this->loggers[$logfile] = $logger;
        }

        return $this->loggers[$logfile];
    }

    /**
     * Returns log directory path with trailing /
     *
     * @return string
     * @throws \Exception
     */
    protected function getLogPath()
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath() . $this->logFilePath;
        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            throw new \Exception('Log Folder could not be generated.');
        }

        return $path;
    }
}
