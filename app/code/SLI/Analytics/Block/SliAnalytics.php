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

namespace SLI\Analytics\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderItemInterface;
use SLI\Analytics\Helper\Data;

class SliAnalytics extends Template
{

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Template\Context $context,
        Session $checkoutSession,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    /**
     * Load the configured client name
     *
     * @return string
     */
    public function getClientDomain()
    {
        $clientDomain = '//' . $this->helper->getClientName() . '.resultspage.com';
        return $clientDomain;
    }

    /**
     * Load data about the products for the last order
     *
     * @return array
     */
    public function getProductData()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $productData = [];
        /** @var OrderItemInterface $orderItem */
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $productData[] = json_encode([
                'id' => $orderItem->getProductId(),
                'price' => $orderItem->getPrice(),
                'quantity' => $orderItem->getQtyOrdered(),
            ]);
        }

        return $productData;
    }

    /**
     * Load transaction data for the latest order
     *
     * @return string
     */
    public function getTransactionData()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $data = [];
        if ($order) {
            $data = [
                'id' => $order->getId(),
                'revenue' => $order->getGrandTotal(),
                'tax' => $order->getTaxAmount(),
                'shipping' => $order->getShippingAmount(),
                'currency' => $order->getOrderCurrency()->getCode()
            ];
        }
        return json_encode($data);
    }
}
