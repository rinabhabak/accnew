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

namespace Mageplaza\GoogleTagManager\Helper;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
//use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as Category;
use Magento\Checkout\Model\Session;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Magento\Catalog\Model\Product\CatalogPrice;

/**
 * Class Data
 * @package Mageplaza\GoogleTagManager\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'googletagmanager';

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $_attributeSet;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_resourceCategory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogHelper;


    /**
     * @var \Magento\Catalog\Model\Product\CatalogPrice
     */
    protected $_catalogPrice;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $resourceCategory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Catalog\Model\Product\CatalogPrice $catalogPrice
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CategoryFactory $categoryFactory,
        Registry $registry,
        AttributeSetRepositoryInterface $attributeSetRepository,
        Category $resourceCategory,
        ProductFactory $productFactory,
        \Magento\Catalog\Helper\Data $catalogHelper,
        CatalogPrice $catalogPrice,
        Session $checkoutSession
    )
    {
        $this->_categoryFactory = $categoryFactory;
        $this->_registry = $registry;
        $this->_checkoutSession = $checkoutSession;
        $this->_attributeSet = $attributeSetRepository;
        $this->_resourceCategory = $resourceCategory;
        $this->_productFactory = $productFactory;
        $this->_catalogHelper = $catalogHelper;
        $this->_catalogPrice = $catalogPrice;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getGtmRegistry()
    {
        return $this->_registry;
    }

    /**
     * Get GTM checkout session
     * @return \Magento\Checkout\Model\Session
     */
    public function getSessionManager()
    {
        return $this->_checkoutSession;
    }

    /**
     * Get GTM ID configure
     * @param null $storeId
     * @return mixed
     */
    public function getTagId($storeId = null)
    {
        return $this->getConfigGeneral('tag_id', $storeId);
    }

    /**
     * Get Store Currency Code. EG:  'currencyCode': 'EUR','USD'
     * @return mixed
     */
    public function getCurrentCurrency()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Measure the additional of a product to a shopping cart.
     * @param $product
     * @param $quantity
     * @return array
     */
    public function getAddToCartData($product, $quantity)
    {
        $data = [
            'event' => 'addToCart',
            'ecommerce' => [
                'currencyCode' => $this->getCurrentCurrency(),
            ]
        ];
        $productData = [];
        $productData['id'] = $product->getSku();
        $productData['sku'] = $product->getSku();
        $productData['name'] = $product->getName();
        $productData['price'] = number_format($product->getFinalPrice(), 2);
        if ($this->getProductBrand($product)) {
            $productData['brand'] = $this->getProductBrand($product);
        }
        if ($this->getColor($product)) {
            $productData['variant'] = $this->getColor($product);
        }
        $productData['quantity'] = $quantity;
        $data ['ecommerce']['add']['products'][] = $productData;

        return $data;
    }

    /**
     * Measure the removal of a product from a shopping cart.
     * @param $product
     * @param $quantity
     * @return array
     */
    public function getRemoveFromCartData($product, $quantity)
    {
        $data = [
            'event' => 'removeFromCart',
            'ecommerce' => [
                'currencyCode' => $this->getCurrentCurrency(),
            ]
        ];
        $productData = [];
        $productData['id'] = $product->getSku();
        $productData['sku'] = $product->getSku();
        $productData['name'] = $product->getName();
        $productData['price'] = number_format($product->getFinalPrice(), 2);
        if ($this->getProductBrand($product)) {
            $productData['brand'] = $this->getProductBrand($product);
        }

        if ($this->getColor($product)) {
            $productData['variant'] = $this->getColor($product);
        }
        $productData['quantity'] = $quantity;
        $data['ecommerce']['remove']['products'] = $productData;

        return $data;
    }

    /**
     * Get data layered in product detail page
     * @param $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductDetailData($product)
    {
        $categoryPath = '';
        $path = $this->getBreadCrumbsPath();
        if (count($path) > 1) {
            array_pop($path);
            $categoryPath = implode(" > ", $path);
        }

        $data = [
            'ecommerce' => [
                'currencyCode' => $this->getCurrentCurrency(),
                'detail' => []
            ]
        ];
        $productData = [];
        $productData['id'] = $product->getSku();
        $productData['sku'] = $product->getSku();
        if ($this->getColor($product)) {
            $productData['variant'][] = $this->getColor($product);
        }
        $productData['name'] = html_entity_decode($product->getName());

        $productData['price'] = number_format($this->_catalogPrice->getCatalogPrice($product), 2);
        if ($this->getProductBrand($product)) {
            $productData['brand'] = $this->getProductBrand($product);
        }
        $productData['attribute_set_id'] = $product->getAttributeSetId();
        $productData['attribute_set_name'] = $this->_attributeSet
            ->get($product->getAttributeSetId())->getAttributeSetName();

        if ($product->getCategory()) {
            $productData['category'] = $product->getCategory()->getName();
        }

        if ($categoryPath) {
            $productData['category_path'] = $categoryPath;
        }

        $data['ecommerce']['detail']['products'][] = $productData;

        return $data;
    }

    /**
     * @param $item
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCheckOutData($item)
    {
        if ($item->getProductType() == "configurable") {
            $selectedProduct = $this->_productFactory->create();
            $selectedProduct->load($selectedProduct->getIdBySku($item->getSku()));
        } else {
            $selectedProduct = $item->getProduct();
        }

        $data['id'] = $selectedProduct->getSku();
        $data['name'] = $selectedProduct->getName();
        $data['sku'] = $selectedProduct->getSku();
        $data['price'] = number_format($selectedProduct->getFinalPrice(), 2);

        if ($this->getColor($selectedProduct)) {
            $data['variant'] = $this->getColor($selectedProduct);
        }
        if ($this->getProductBrand($selectedProduct)) {
            $data['brand'] = $this->getProductBrand($selectedProduct);
        }
        $data['category'] = $this->getCategoryName($selectedProduct);

        $data['attribute_set_id'] = $selectedProduct->getAttributeSetId();
        $data['attribute_set_name'] = $this->_attributeSet
            ->get($selectedProduct->getAttributeSetId())->getAttributeSetName();

        return $data;
    }

    /**
     * @param $item
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductOrderedData($item)
    {
        if ($item->getProductType() == "configurable") {
            $selectedProduct = $this->_productFactory->create();
            $selectedProduct->load($selectedProduct->getIdBySku($item->getSku()));
        } else {
            $selectedProduct = $item->getProduct();
        }

        $data['id'] = $selectedProduct->getSku();
        $data['sku'] = $selectedProduct->getSku();
        $data['name'] = $selectedProduct->getName();
        $data['price'] = number_format($item->getBasePrice(), 2);
        if ($this->getColor($selectedProduct)) {
            $data['variant'] = $this->getColor($selectedProduct);
        }
        if ($this->getProductBrand($selectedProduct)) {
            $data['brand'] = $this->getProductBrand($selectedProduct);
        }
        if ($selectedProduct->getCategory()) {
             $data['category'] = $selectedProduct->getCategory()->getName();
        }else{
            $data['category'] = $this->getCategoryName($selectedProduct);
        }
        $data['attribute_set_id'] = $selectedProduct->getAttributeSetId();
        $data['attribute_set_name'] = $this->_attributeSet->get($selectedProduct->getAttributeSetId())->getAttributeSetName();
        $data['quantity'] = number_format($item->getQtyOrdered(), 0);

        return $data;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAffiliationName()
    {
        $webName = $this->storeManager->getWebsite()->getName();
        $groupName = $this->storeManager->getGroup()->getName();
        $storeName = $this->storeManager->getStore()->getName();

        return $webName . '-' . $groupName . '-' . $storeName;
    }

    /**
     * Check the following modules is installed
     * @param $moduleName
     * @return bool
     */
    public function moduleIsEnable($moduleName)
    {
        $result = false;
        if ($this->_moduleManager->isEnabled($moduleName)) {
            switch ($moduleName) {
                case 'Mageplaza_Shopbybrand' :
                    $result = true;
                    break;
                case 'Mageplaza_Osc' :
                    $oscHelper = $this->objectManager->create('\Mageplaza\Osc\Helper\Data');
                    $result = ($oscHelper->isEnabled()) ? true : false;
                    break;
            }
        }

        return $result;
    }

    /**
     * Get product brand if module Mageplaza_Shopbybrand is installed
     * @param $product
     * @return null
     */
    public function getProductBrand($product)
    {
        $attCode = null;
        if ($this->moduleIsEnable('Mageplaza_Shopbybrand')) {

            $sbbHelper = $this->objectManager->create('\Mageplaza\Shopbybrand\Helper\Data');
            $brandFactory = $this->objectManager->create('\Mageplaza\Shopbybrand\Model\BrandFactory');
            if (!$sbbHelper->getGeneralConfig('enabled') || !$sbbHelper->getAttributeCode()) {
                return 'Accuride';
            } else {
                $attCode = $sbbHelper->getAttributeCode();
                if ($this->_request->getFullActionName() == 'checkout_index_index') {

                    $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                        ->load($product->getId());
                }
                $brand = $brandFactory->create()->loadByOption($product->getData($attCode))->getValue();

                return $brand;
            }
        }elseif ($product->getAttributeText('manufacturer')) {
            return $product->getAttributeText('manufacturer');
        }
        return 'Accuride';
    }

    /**
     * Get color of configurable and simple product
     * @param $product
     * @return array|null|string
     */
    public function getColor($product)
    {
        $color = [];

        switch ($product->getTypeId()) {
            case 'configurable' :
                $configurationAtt = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
                foreach ($configurationAtt as $att) {
                    if ($att['label'] == 'Color') {
                        foreach ($att['values'] as $value) {
                            $color[] = $value['label'];
                        }
                        break;
                    }
                }
                $color = implode(',', $color);

                return $color;
            case 'simple' :
                $table = $this->objectManager->create('Magento\Eav\Model\Entity\Attribute\Source\Table');
                $eavAttribute = $this->objectManager->get('\Magento\Eav\Model\Entity\Attribute');
                $colorAttribute = $eavAttribute->load($eavAttribute->getIdByCode('catalog_product', 'color'));
                $allColor = $table->setAttribute($colorAttribute)->getAllOptions(false);
                foreach ($allColor as $color) {
                    if ($color['value'] == $product->getData('color')) {
                        return $color['label'];
                    }
                }

                return null;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getBreadCrumbsPath()
    {
        $path = [];
        $breadCrumbs = $this->_catalogHelper->getBreadcrumbPath();
        foreach ($breadCrumbs as $breadCrumb) {
            $path [] = $breadCrumb['label'];
        }

        return $path;
    }

    /**
     * @param $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryName($item)
    {
        $categoryName = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_Configurable = $objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
        $parentproduct= $_Configurable->getParentIdsByChild($item->getId());
        $_productParent= $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        if (sizeof($parentproduct)){
            $parent= $_productParent->getById($parentproduct[0]);
            $categoryIds = $parent->getCategoryIds();
        }else{
        	$categoryIds = $item->getCategoryIds();
        }
        if(sizeof($categoryIds)>1){
          $categories = $this->getCategoryCollection()
              ->addAttributeToFilter('entity_id', $categoryIds);
          foreach ($categories as $category) {
              $categoryName[] = $category->getName();
          }
          if (in_array('Shop', $categoryName)) {
              foreach ($categoryName as $key => $value) {
                  if ($value == 'Shop') {
                      unset($categoryName[$key]);
                  }
              }
          }
          return implode(',', $categoryName);
        }
        return 'Default';
    }

    /**
     * @param bool $isActive
     * @param bool $level
     * @param bool $sortBy
     * @param bool $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_resourceCategory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }
        return $collection;
    }
}
