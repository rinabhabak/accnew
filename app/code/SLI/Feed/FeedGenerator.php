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

use Psr\Log\LoggerInterface;
use SLI\Feed\Helper\GeneratorHelper;
use SLI\Feed\Helper\XmlWriter;
use SLI\Feed\Logging\LoggerFactoryInterface;
use SLI\Feed\Model\Generators\GeneratorInterface;

/**
 * Generator for a full store feed.
 *
 * @package SLI\Feed
 */
class FeedGenerator
{
    /**
     * Logger factory
     *
     * @var LoggerFactoryInterface
     */
    protected $loggerFactory;

    /**
     * List of generators.
     */
    protected $generators;

    /**
     * Feed generation helper
     *
     * @var GeneratorHelper
     */
    protected $generatorHelper;

    /**
     * @param array $generators
     * @param LoggerFactoryInterface $loggerFactory
     * @param GeneratorHelper $generatorHelper
     */
    public function __construct(array $generators, LoggerFactoryInterface $loggerFactory, GeneratorHelper $generatorHelper)
    {
        $this->generators = $generators;
        $this->loggerFactory = $loggerFactory;
        $this->generatorHelper = $generatorHelper;
    }

    /**
     * Generate a feed for a certain store ID.
     *
     * @param int $storeId
     * @param string $filename The filename to write to
     * @return bool
     * @throws \Exception
     */
    public function generateForStoreId($storeId, $filename)
    {
        /** @var GeneratorInterface $generator */
        $generator = null;
        $result = true;
        $totalStart = time();
        $logger = $this->loggerFactory->getStoreLogger($storeId);

        $this->generatorHelper->logSystemSettings($logger);

        try {
            $logger->info(sprintf('Starting Feed generation for storeId [%s], filename: %s', $storeId, $filename));

            $xmlWriter = new XmlWriter($filename);

            foreach ($this->generators as $generator) {
                $start = time();
                if (!$result = $generator->generateForStoreId($storeId, $xmlWriter, $logger)) {
                    break;
                }
                $end = time();
                $logger->debug(sprintf('[%s] %s: %s; duration: %s sec, start: %s, end: %s',
                    $storeId, get_class($generator), $result ? 'success' : 'failed', $end - $start, $start, $end
                ));
            }

            $xmlWriter->closeFeed();
        } catch (\Exception $e) {
            $logger->error(sprintf('[%s] %s feed generation failed: %s',
                $storeId, get_class($generator), $e->getMessage()),
                ['exception' => $e]
            );

            if ('cli' != php_sapi_name()) {
                // rethrow exception so that it can be handled by FeedManager and show it in UI
                throw new \Exception($e);
            }

            $result = false;
        }

        $totalEnd = time();
        $logger->info(sprintf('[%s] Finish Feed generation: %s, duration: %s sec, start: %s, end: %s',
            $storeId, $result ? 'success' : 'failed', $totalEnd - $totalStart, $totalStart, $totalEnd
        ));

        return $result;
    }
}
