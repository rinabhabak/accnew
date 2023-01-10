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
use Magento\Framework\ObjectManagerInterface;
use Mageplaza\GoogleTagManager\Helper\Data;

/**
 * Class AddToCart
 * @package Mageplaza\GoogleTagManager\Observer
 */
class AddToCart implements ObserverInterface
{
    /**
     * @var \Mageplaza\GoogleTagManager\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * AddToCart constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Mageplaza\GoogleTagManager\Helper\Data $helper
     */
    public function __construct(
        ProductFactory $productFactory,
        ObjectManagerInterface $objectManager,
        Data $helper
    )
    {
        $this->_productFactory = $productFactory;
        $this->_objectManager  = $objectManager;
        $this->_helper         = $helper;
    }

    /**
     * Catch add to cart event
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if ($this->_helper->isEnabled()) {
            $product = $observer->getData('product');
            $request = $observer->getData('request');

            $qty = $request->getParam('qty');
            if ($product->getTypeId() == "configurable") {
                $selectedProduct = $this->_productFactory->create();
                $selectedProduct->load($selectedProduct->getIdBySku($product->getSku()));
                $this->_helper->getSessionManager()->setAddToCartData($this->_helper->getAddToCartData($selectedProduct, $qty));
            } else {
                $this->_helper->getSessionManager()->setAddToCartData($this->_helper->getAddToCartData($product, $qty));
            }
        }

        return $this;
    }
}