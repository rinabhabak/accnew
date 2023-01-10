<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GoogleTagManager
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GoogleTagManager\Observer;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\GoogleTagManager\Helper\Data;

/**
 * Class RemoveFromCart
 * @package Mageplaza\GoogleTagManager\Observer
 */
class RemoveFromCart implements ObserverInterface
{
    /**
     * @var \Mageplaza\GoogleTagManager\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * RemoveFromCart constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Mageplaza\GoogleTagManager\Helper\Data $helper
     */
    public function __construct(
        ProductFactory $productFactory,
        Data $helper
    )
    {
        $this->_productFactory = $productFactory;
        $this->_helper         = $helper;
    }

    /**
     * Catch remove from cart event
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if ($this->_helper->isEnabled()) {
            $quoteItem = $observer->getData('quote_item');
            $qty       = $quoteItem->getQty();

            if ($quoteItem->getProductType() == "configurable") {
                $selectedProduct = $this->_productFactory->create();
                $selectedProduct->load($selectedProduct->getIdBySku($quoteItem->getSku()));
                $this->_helper->getSessionManager()->setRemoveFromCartData($this->_helper->getRemoveFromCartData($selectedProduct, $qty));
            } else {
                $this->_helper->getSessionManager()->setRemoveFromCartData($this->_helper->getRemoveFromCartData($quoteItem, $qty));
            }
        }

        return $this;
    }
}