<?php
/**
 * Alpine_Ga
 *
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc
 * @author      Danila Vasenin <danila.vasenin@alpineinc.com>
 */

namespace Alpine\Ga\Block;

use Magento\Catalog\Model\Category;

/**
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class ListJson extends \Magento\GoogleTagManager\Block\ListJson
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\GoogleTagManager\Helper\Data $helper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Helper\Cart $checkoutCart
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Banner\Model\ResourceModel\Banner\CollectionFactory $bannerColFactory
     * @param \Magento\GoogleTagManager\Model\Banner\Collector $bannerCollector
     * @param array $data
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\GoogleTagManager\Helper\Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Helper\Cart $checkoutCart,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Banner\Model\ResourceModel\Banner\CollectionFactory $bannerColFactory,
        \Magento\GoogleTagManager\Model\Banner\Collector $bannerCollector,
        array $data = [],
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory

    ) {
        parent::__construct($context, $helper, $jsonHelper, $registry, $checkoutSession, $customerSession, $checkoutCart, $layerResolver, $moduleManager, $request, $bannerColFactory, $bannerCollector, $data );
        $this->_categoryCollectionFactory = $categoryCollectionFactory;

    }

    /**
     * Format product item for output to json
     *
     * @param $item \Magento\Quote\Model\Quote\Item
     * @return array
     */
    protected function _formatProduct($item)
    {
        $product = [];
        $product['id'] = $item->getSku();
        $product['name'] = $item->getName();
        $product['price'] = $item->getPrice();
        $product['qty'] = $item->getQty();
        $product['brand'] = $item->getProduct()->getData('manufacture');
        $product['category'] = $this->_getProductCategories($item->getProduct());

        return $product;
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

    /**
     * Overrides the original method to make it more robust against
     * xml configuration issues and prevent fatal errors in such cases
     *
     *   - in the original method there a calls made to $this->getListBlock(),
     *     but if the child is not configured in layout xml this causes a
     *     fatal error and crashes the entire website. Issue is resolved by simply
     *     checking if this is configured and the object exists first
     *
     * @inheritdoc
     */
    protected function _getProductCollection()
    {
        /** @var \Magento\Catalog\Block\Product\ListProduct $listBlock */
        $listBlock = $this->getListBlock();

        if ($listBlock) {
            return parent::_getProductCollection();
        }

        return $this->_productCollection;
    }
}
