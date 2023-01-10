<?php
/**
 * Alpine_SLISpark
 *
 * @category    Alpine
 * @package     Alpine_SLISpark
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc
 * @author      Aleksandr Mikhailov (aleksandr.mikhailov@alpineinc.com)
 * @author      Evgeniy Derevyanko (evgeniy.derevyanko@alpineinc.com)
 * @author      Andrey Nesterov (andrey.nesterov@alpineinc.com)
 */

namespace Alpine\SLISpark\Block;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Element\Template;

/**
 * Class SparkConfig
 *
 * @category Alpine
 * @package  Alpine_SLISpark
 */
class SparkConfig extends Template
{
    /**
     * Prefix for "transaction id" for step 3
     *
     * @var string
     */
    const TRANSACTION_PREFIX = 'ACCU-';

    /**
     * Cart
     *
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * DataObject Factory
     *
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    protected $helper;

    /**
     * SparkConfig constructor
     *
     * @param Template\Context             $context
     * @param \Magento\Checkout\Model\Cart $cart
     * @param DataObjectFactory            $dataObjectFactory
     * @param array                        $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        DataObjectFactory $dataObjectFactory,
        \Alpine\SLISpark\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cart = $cart;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->helper = $helper;
    }

    /**
     * Get data for sli-spark
     *
     * @return DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSparkConfig()
    {
        $productsList = $this->dataObjectFactory->create();

        $productsList->setProducts($this->getProductsFromOrder());
        $productsList->setConfig($this->getConfig());

        return $productsList;
    }

    /**
     * Get spark js
     *
     * @return string
     */
    public function getSparkJs()
    {
        return $this->helper->getJsConfig();
    }

    /**
     * Get part of config with product items
     *
     * @return array
     */
    protected function getProductsFromOrder()
    {
        $inSuccessPage = $this->inSuccessPage();

        $isource = $this->cart->getQuote();
        if ($inSuccessPage) {
            $isource =
                $this->cart
                    ->getCheckoutSession()
                    ->getLastRealOrder();
        }
        $cartItems         = $isource->getAllVisibleItems();
        $productsFromOrder = [];
        /** @var \Magento\Quote\Model\Quote\Item $cartItem */
        foreach ($cartItems as $cartItem) {
            $productId = $cartItem->getProductId();

            if ($cartItem->getParentItemId()) {
                $productId = $cartItem->getParentItem()->getProductId();
            }

            $productsFromOrder[] = [
                'id'       => $productId,
                'price'    => $cartItem->getPrice(),
                'quantity' => $inSuccessPage ? $cartItem->getQtyOrdered() : $cartItem->getQty(),
                'currency' => 'USD',
            ];
        }
        return $productsFromOrder;
    }

    /**
     * Get part of config for "transaction"
     *
     * @return array
     */
    protected function getConfig()
    {
        $inSuccessPage = $this->inSuccessPage();

        $isource = $this->cart->getQuote();
        if ($inSuccessPage) {
            $isource =
                $this->cart
                    ->getCheckoutSession()
                    ->getLastRealOrder();
        }

        $config = [
            'id'       => '',
            'revenue'  => '',
            'tax'      => '',
            'shipping' => '',
            'currency' => 'USD',
        ];

        if ($inSuccessPage) {
            $config['id'] = self::TRANSACTION_PREFIX . $isource->getRealOrderId();
        }
        $config['revenue'] = $isource->getGrandTotal();

        if ($inSuccessPage) {
            $config['tax']      = $isource->getTaxAmount();
            $config['shipping'] = $isource->getShippingAmount();
        } else {
            $shippingAddress = $isource->getShippingAddress();
            if ($shippingAddress) {
                $config['tax']      = $shippingAddress->getTaxAmount();
                $config['shipping'] = $shippingAddress->getShippingAmount();
            }
        }

        return $config;
    }

    /**
     * Check if we in order confirmation page
     *
     * @return bool
     */
    public function inSuccessPage()
    {
        return $this->_request->getFullActionName() === "checkout_onepage_success";
    }

    /**
     * decode method
     * 
     * @param string $str
     */
    public function blockUnicodeDecode($str) {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        }, $str);
    }
}
