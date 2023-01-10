<?php
/**
 * Alpine_Ga
 *
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc
 * @author      Danila Vasenin <danila.vasenin@alpineinc.com>
 */

namespace Alpine\Ga\Block;

/**
 * @api
 * @since 100.0.2
 */
class Ga extends \Magento\GoogleTagManager\Block\Ga
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection
     * @param \Magento\GoogleTagManager\Helper\Data $googleAnalyticsData
     * @param \Magento\Cookie\Helper\Cookie $cookieHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection,
        \Magento\GoogleTagManager\Helper\Data $googleAnalyticsData,
        \Magento\Cookie\Helper\Cookie $cookieHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = [],
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory

    ){
        parent::__construct($context, $salesOrderCollection, $googleAnalyticsData, $cookieHelper, $jsonHelper, $data);
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Return information about order and items
     *
     * @return array
     * @since 100.2.0
     */
    public function getOrdersDataArray()
    {
        $result = [];
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return $result;
        }
        $collection = $this->_salesOrderCollection->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orderIds]);

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($collection as $order) {
            $orderData = [
                'id' => $order->getIncrementId(),
                'revenue' => $order->getBaseGrandTotal() -
                    ($order->getBaseTaxAmount() + $order->getBaseShippingAmount()),
                'tax' => $order->getBaseTaxAmount(),
                'shipping' => $order->getBaseShippingAmount(),
                'coupon' => (string)$order->getCouponCode()
            ];

            $products = [];
            /** @var \Magento\Sales\Model\Order\Item $item*/
            foreach ($order->getAllVisibleItems() as $item) {
                $products[] = [
                    'id' => $item->getSku(),
                    'name' => $item->getName(),
                    'price' => $item->getBasePrice(),
                    'quantity' => $item->getQtyOrdered(),
                    'brand' => $item->getProduct()->getData('manufacture'),
                    'category' => $this->_getProductCategories($item->getProduct())
                ];
            }

            $result[] = [
                'ecommerce' => [
                    'purchase' => [
                        'actionField' => $orderData,
                        'products' => $products
                    ],
                    'currencyCode' => $this->getStoreCurrencyCode()
                ],
                'event' => 'purchase'
            ];
        }
        return $result;
    }

    /**
     * Return comma separated category names of a given product
     *
     * @return array
     */
    protected function _getProductCategories($product)
    {
        $categoryIds = $product->getCategoryIds();

        $categories = $this->_categoryCollectionFactory->create()
            ->addAttributeToFilter('entity_id', $categoryIds)
            ->addAttributeToSelect('name');

        $names = [];

        foreach ($categories as $category) {
            $names[] = $category->getName();
        }

        return implode(',', $names);
    }
}
