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

namespace SLI\Api\Controller\Api;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use /** @noinspection PhpUndefinedClassInspection Dynamically created Factory */
    Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Customer\Model\Session;
use SLI\Api\Helper\CartData;

/**
 * Adminhtml feed controller
 */
class Cart extends Action
{
    /** @noinspection PhpUndefinedClassInspection */
    /**
     * @var JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * The contents of the cart.
     * @var CartData
     */
    protected $cartData;

    /** @noinspection PhpUndefinedClassInspection */
    /**
     * @param Context $context
     * @param JsonFactory $jsonResultFactory
     */
    public function __construct(
        Context $context,
        /** @noinspection PhpUndefinedClassInspection */
        JsonFactory $jsonResultFactory
    ) {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
    }

    /**
     * Return a JSON Result page containing the cart contents for the current user session
     * @return Json a results page containing json data representing the cart info
     */
    public function execute()
    {
        /* @var $session Session */
        $session = $this->_objectManager->get('Magento\Customer\Model\Session');

        /* @var $cartData CartData */
        $cartData = $this->_objectManager->get('SLI\Api\Helper\CartData');
        $key = $cartData->getInfo($session);

        /* @var $jsonPage Json */
        /** @noinspection PhpUndefinedMethodInspection */
        $jsonPage = $this->jsonResultFactory->create();
        $jsonPage->setData($key);

        return $jsonPage;
    }
}
