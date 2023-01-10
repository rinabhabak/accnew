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
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GoogleTagManager\Block;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Cookie\Helper\Cookie as HelperCookie;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\GoogleTagManager\Helper\Data as HelperData;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product\CatalogPrice;

/**
 * Class TagManager
 * @package Mageplaza\GoogleTagManager\Block
 */
class TagManager extends Template
{
    /**
     * @var HelperData
     */
    protected $_helper;

    /**
     * @var HelperCookie
     */
    protected $_helperCookie;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogHelper;

    /**
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    protected $_listProduct;

    /**
     * @var \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    protected $_toolbar;


    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;


    /**
     * @var \Magento\Catalog\Model\Product\CatalogPrice
     */
    protected $_catalogPrice;

    /**
     * TagManager constructor.
     * @param ProductFactory $productFactory
     * @param CategoryFactory $categoryFactory
     * @param Data $catalogHelper
     * @param ListProduct $listProduct
     * @param Toolbar $toolbar
     * @param Context $context
     * @param HelperData $helper
     * @param HelperCookie $helperCookie
     * @param Registry $registry
     * @param CatalogPrice $catalogPrice
     * @param ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        Data $catalogHelper,
        ListProduct $listProduct,
        Toolbar $toolbar,
        Context $context,
        HelperData $helper,
        HelperCookie $helperCookie,
        Registry $registry,
        CatalogPrice $catalogPrice,
        ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        $this->_catalogHelper = $catalogHelper;
        $this->_productFactory = $productFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_helper = $helper;
        $this->_helperCookie = $helperCookie;
        $this->_objectManager = $objectManager;
        $this->_listProduct = $listProduct;
        $this->_toolbar = $toolbar;
        $this->_registry = $registry;
        $this->_catalogPrice = $catalogPrice;
        parent::__construct($context, $data);
    }

    /**
     * Get GTM Id
     * @param null $storeId
     * @return mixed
     */
    public function getTagId($storeId = null)
    {
        return $this->_helper->getTagId($storeId);
    }

    /**
     * Check condition show page
     * @return bool
     */
    public function checkConditionShowPage()
    {
        if (!$this->_helperCookie->isUserNotAllowSaveCookie() && $this->_helper->isEnabled()) {
            return true;
        }

        return false;
    }

    /**
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPageInfo()
    {
        $action = $this->getRequest()->getFullActionName();

        switch ($action) {
            case 'catalog_category_view': // Product list page
                /** get current breadcrumb path name */
                $path = $this->_helper->getBreadCrumbsPath();
                $products = [];
                $result = [];
                $i = 0;

                $categoryId = $this->_registry->registry('current_category')->getId();
                $category   = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
                $loadedProduct = $category->getProductCollection()->addAttributeToSelect('*');
                $loadedProduct->setCurPage($this->getPageNumber())->setPageSize($this->getPageLimit());

                foreach ($loadedProduct as $item) {
                    $i++;
                    $products[$i]['id'] = $item->getSku();
                    $products[$i]['name'] = $item->getName();

                    $products[$i]['price'] = number_format($this->_catalogPrice->getCatalogPrice($item), 2);
                    if ($this->_helper->getProductBrand($item)) {
                        $products[$i]['brand'] = $this->_helper->getProductBrand($item);
                    }
                    if ($this->_helper->getColor($item)) {
                        $products[$i]['variant'] = $this->_helper->getColor($item);
                    }
                    $products[$i]['path'] = implode(" > ", $path) . " > " . $item->getName();
                    $products[$i]['category_path'] = implode(" > ", $path);
                    $result [] = $products[$i];
                }

                $data ['ecommerce'] = [
                    'currencyCode' => $this->_helper->getCurrentCurrency(),
                    'impressions' => $result
                ];

                return $data;
            case 'catalog_product_view': // Product detail view page
                $currentProduct = $this->_helper->getGtmRegistry()->registry('product');
                $data = $this->_helper->getProductDetailData($currentProduct);
                return $data;
            case 'checkout_index_index': // Shopping cart / Checkout page
            case 'checkout_cart_index':
                return $this->getCheckoutProductData();
            case 'onestepcheckout_index_index': // Mageplaza One step check out page
                if (!$this->_helper->moduleIsEnable('Mageplaza_Osc')) {
                    return null;
                } else {
                    return $this->getCheckoutProductData();
                }
            case 'checkout_onepage_success': // Purchase page
            case 'multishipping_checkout_success':
                $order = $this->_helper->getSessionManager()->getLastRealOrder();
                $products = [];
                $items = $order->getItemsCollection([],true);

                foreach ($items as $item) {
                    $products[] = $this->_helper->getProductOrderedData($item);
                }
                $data ['ecommerce'] = [
                    'purchase' => [
                        'actionField' => [
                            'id' => $order->getIncrementId(),
                            'affiliation' => $this->_helper->getAffiliationName(),
                            'order_id' => $order->getIncrementId(),
                            'subtotal' => number_format($order->getSubtotal(), 2),
                            'shipping' => number_format($order->getBaseShippingAmount(), 2),
                            'tax' => number_format($order->getBaseTaxAmount(), 2),
                            'total' => number_format($order->getSubtotal(), 2),
                            'revenue' => number_format($order->getSubtotal(), 2),
                            'discount' => number_format($order->getDiscountAmount(), 2),
                            'coupon' => (string)$order->getCouponCode()
                        ],
                        'products' => $products
                    ]
                ];

                
                $data ['ecommerce']['products'] = $products;

                return $data;
        }

        return null;
    }

    /**
     * Get the page limit in category product list page
     * @return int
     */
    public function getPageLimit()
    {
        $result = ($this->_toolbar) ? $this->_toolbar->getLimit() : 9;

        return (int)$result;
    }

    /**
     * Get the current page number of category product list page
     * @return int|mixed
     */
    public function getPageNumber()
    {
        $result = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;

        return $result;
    }

    /**
     * Get AddToCartData data layered from checkout session
     * @return null|string
     */
    public function getAddToCartData()
    {
        if ($this->_helper->getSessionManager()->getAddToCartData()) {
            $data = json_encode($this->_helper->getSessionManager()->getAddToCartData());

            return $data;
        }

        return null;
    }

    /**
     * Remove AddToCartData data layered from checkout session
     */
    public function removeAddToCartData()
    {
        $this->_helper->getSessionManager()->setAddToCartData(null);
    }

    /**
     * Get RemoveFromCartData from checkout session
     * @return null|string
     */
    public function getRemoveFromCartData()
    {
        if ($this->_helper->getSessionManager()->getRemoveFromCartData()) {
            $data = json_encode($this->_helper->getSessionManager()->getRemoveFromCartData());

            return $data;
        }

        return null;
    }

    /**
     * Remove RemoveFromCartData from checkout session
     */
    public function removeRemoveFromCartData()
    {
        $this->_helper->getSessionManager()->setRemoveFromCartData(null);
    }

    /**
     * Get product data in checkout page
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCheckoutProductData()
    {
        $cart = $this->_objectManager->get('\Magento\Checkout\Model\Cart');
        // retrieve quote items array
        $items = $cart->getQuote()->getAllVisibleItems();
        $products = [];
        $data ['event'] = 'checkout';

        $checkoutData ['actionField'] = ['step' => 0, 'option' => ''];
        $checkoutData ['hasItems'] = $cart->getQuote()->hasData();
        $checkoutData ['hasCoupon'] = ($cart->getQuote()->getCouponCode()) ? true : false;
        $checkoutData ['coupon'] = ($cart->getQuote()->getCouponCode()) ? $cart->getQuote()->getCouponCode() : "";
        $checkoutData ['total'] = number_format($cart->getQuote()->getData('grand_total'), 2);

        foreach ($items as $item) {
            $products[] = $this->_helper->getProductCheckOutData($item);
        }
        $data ['ecommerce']['checkout'] = $checkoutData;
        $data ['ecommerce']['products'] = $products;

        return $data;
    }
}