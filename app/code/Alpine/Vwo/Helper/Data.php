<?php
/**
 * Alpine Vwo Helper
 *
 * @category    Alpine
 * @package     Alpine_Vwo
 * @copyright   Copyright (c) 2019 Alpine Consulting Inc.
 * @author      Alex Didenko <alex.didenko@alpineinc.com>
 */

namespace Alpine\Vwo\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Checkout\Model\Session;

/**
 * Alpine_Vwo
 *
 * @category    Alpine
 * @package     Alpine_Vwo
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Checkout Session
     *
     * @var Session
     */
    protected $checkout;

    /**
     * Data constructor
     *
     * @param Context $context
     * @param Checkout $checkout
     */
    public function __construct(
        Context $context,
        Session $checkout
    ) {
        parent::__construct($context);
        $this->checkout = $checkout;
    }

    /**
     * Get actual revenue
     *
     * @return string
     */
    public function getRevenue()
    {
        $order = $this->checkout->getLastRealOrder();

        return $order->getSubtotal();
    }
}
