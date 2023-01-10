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

namespace SLI\Cors\Observer;

use Exception;
use Magento\Framework\App\Action\Action;
use Psr\Log\LoggerInterface;
use SLI\Cors\Helper\CorsHelper;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Zend\Http\PhpEnvironment\Request;

class CorsObserver implements ObserverInterface
{

    const ENABLE_HEADER = 'x-requested-with';

    /**
     * The cors helper
     * @var CorsHelper
     */
    protected $corsHelper;

    /**
     * A logger
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CartObserver constructor.
     * @param CorsHelper $corsHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        CorsHelper $corsHelper,
        LoggerInterface $logger
    ) {
        $this->corsHelper = $corsHelper;
        $this->logger = $logger;
    }

    /**
     * Execution of the Observer.
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            if ($this->corsHelper->isCorsEnabled()) {
                $this->addCORS($observer);
            }
        } catch (Exception $e) {
            $this->logger->error(sprintf("CorsObserver could not add Header because: %s . %s", $e->getMessage(), $e));
        }
    }

    /**
     * Add cors to the observed event's response
     * @param Observer $observer
     * @return void
     */
    protected function addCORS(Observer $observer)
    {
        // Un-pack the Event into Request and Response
        $eventData = $observer->getEvent();
        /** @var Action $controller */
        $controller = $eventData->getData('controller_action');

        /** @var RequestInterface $request */
        $request = $controller->getRequest();
        /** @var ResponseInterface $response */
        $response = $controller->getResponse();

        // Verify we should intercept this request, else exit.
        if (!$this->verify($request, $response)) {
            return;
        };
        /**
         * Verified response is of type HttpInterface in verifyRequest, so change its type.
         * @var HttpInterface $response
         */

        $origin = $this->getCorsOrigin($request);
        // Make this safe, by setting 3rd parameter to false.
        if (!empty($origin)) {
            $response->setHeader("Access-Control-Allow-Origin", $origin, false);
            $response->setHeader("Access-Control-Allow-Credentials", "true", false);

            $requestHeader = $request->getHeader("Access-Control-Request-Headers");
            if (strpos($requestHeader, static::ENABLE_HEADER) !== false) {
                $response->setHeader("Access-Control-Allow-Headers", static::ENABLE_HEADER, false);
            }
        }
    }

    /**
     * Ensure the event captured meets requirements:
     * - Request was from a domain matching the configured Subdomain
     * - Response is of a HTTP type.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool false if the request should be ignored.
     */
    private function verify(RequestInterface $request, ResponseInterface $response)
    {
        // Ensure the Response is HTTP so we can set headers.
        if (!$response instanceof HttpInterface) {
            return false;
        }

        // Ensure the request is from the authorised origin
        $origin = $this->getRequestOrigin($request);
        // If origin is not "" and configured subdomain is a match for the origin
        if (empty($origin) || !$this->corsHelper->checkSubdomain($origin)) {
            return false;
        }

        return true;
    }

    /**
     * Gets the origin for the request.
     * @param RequestInterface $request  the request
     * @return string
     */
    protected function getRequestOrigin(RequestInterface $request)
    {
        $origin = "";
        if ($request instanceof Request) {
            /** @var \Zend\Http\PhpEnvironment\Request $request */
            $origin = $request->getServer("HTTP_ORIGIN");
            if (is_array($origin)) {
                return "";
            } // couldn't find HTTP_ORIGIN
        }
        return $origin;
    }

    /**
     * Returns the origin if it matches the approved list.
     * @param RequestInterface $request the request
     * @return string the origin of the request, or ""
     */
    protected function getCorsOrigin($request)
    {
        $origin = $this->getRequestOrigin($request);
        if ($this->corsHelper->checkSubdomain($origin)) {
            return $origin;
        }
        return "";
    }
}