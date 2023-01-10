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

use Psr\Log\LoggerInterface;
use Monolog\Handler\HandlerInterface;

/**
 * Class LoggerFactoryInterface
 *
 * @package SLI\Feed\Logging
 */
interface LoggerFactoryInterface
{
    // types
    const GENERAL_LOGGER = 'generalLogger';
    const STORE_LOGGER = 'storeLogger';

    /**
     * Get the general logger.
     *
     * @return LoggerInterface
     */
    public function getGeneralLogger();

    /**
     * Get a logger for the given storeId.
     *
     * @param string $storeId
     * @param string $prefix
     * @return LoggerInterface
     */
    public function getStoreLogger($storeId, $prefix = '');

    /**
     * Add an additional logger handler.
     *
     * @param HandlerInterface $handler The handler
     * @param string $type The logger type.
     */
    public function addLoggerHandler(HandlerInterface $handler, $type);
}
