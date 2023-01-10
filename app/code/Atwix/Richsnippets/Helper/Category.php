<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Helper;

use Atwix\Richsnippets\Helper\Data as SnippetsHelper;
use Atwix\Richsnippets\Helper\Product as ProductHelper;
use Magento\Catalog\Helper\Image as ProductImageHelper;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ProductFactory as ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Registry;
use Magento\Framework\Url as Url;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory as SummaryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Escaper;
use Atwix\Richsnippets\Service\SerializerService;

class Category extends SnippetsHelper
{
    const PRODUCT_PRICE_ATTR = 'price';
    const PRODUCT_IMAGE_ID = 'product_base_image';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Filter manager
     *
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var SummaryCollectionFactory
     */
    protected $summaryCollectionFactory;

    /**
     * Array of same attributes for product
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    protected $attributes;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $categoryProductCollection = null;

    /**
     * @var string
     */
    protected $categoryDescription = null;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $category = null;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var ProductImageHelper
     */
    protected $productImageHelper;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;


    /**
     * Category constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param FilterManager $filterManager
     * @param Url $url
     * @param SummaryCollectionFactory $summaryCollectionFactory
     * @param EavConfig $eavConfig
     * @param ProductCollectionFactory $productCollectionFactory
     * @param PricingHelper $pricingHelper
     * @param ProductImageHelper $productImageHelper
     * @param ProductFactory $productFactory
     * @param SerializerService $serializerService
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Registry $registry,
        FilterManager $filterManager,
        Url $url,
        SummaryCollectionFactory $summaryCollectionFactory,
        EavConfig $eavConfig,
        ProductCollectionFactory $productCollectionFactory,
        PricingHelper $pricingHelper,
        ProductImageHelper $productImageHelper,
        ProductFactory $productFactory,
        Escaper $escaper,
        SerializerService $serializerService
    ) {
        parent::__construct($context, $storeManager, $url, $serializerService);
        $this->registry = $registry;
        $this->filterManager = $filterManager;
        $this->summaryCollectionFactory = $summaryCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->pricingHelper = $pricingHelper;
        $this->productImageHelper = $productImageHelper;
        $this->productFactory = $productFactory;
        $this->escaper = $escaper;
    }

    /**
     * Returns currently viewed category
     *
     * @return \Magento\Catalog\Model\Category
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategory()
    {
        if (is_null($this->category)) {
            if (!$category = $this->registry->registry('current_category')) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Make sure that Category exist.')
                );
            }
            $this->category = $category;
        }
        return $this->category;
    }

    /*
     * Generate category snippets
     *
     * @return array
     */
    public function generateCategorySnippets()
    {
        $snippets = array(
            'name',
            'description',
            'thumbnail',
            'offers',
            'availability',
            'rating'
        );
        if(!$category = $this->getCategory()) {
            return $snippets;
        }
        $snippets = array(
            'name'          =>  $category->getName(),
            'description'   =>  $this->getDescription(),
            'thumbnail'     =>  $this->getImage(),
            'offers'        =>  $this->getPrices(),
            'availability'  =>  __('In stock')->getText(),
            'rating'        =>  $this->getRatings()
        );
        return $snippets;
    }

    /*
     * Get category description
     *
     * @return string
     */
    protected function getDescription()
    {
        if (is_null($this->categoryDescription)) {
            $this->categoryDescription = '';
            if(!$this->getConfigurationValue('category/description')) {
                return $this->categoryDescription;
            }
            if(!$category = $this->getCategory()) {
                return $this->categoryDescription;
            }

            $this->categoryDescription = $category->getDescription();

            if (!$this->categoryDescription) {
                $categoryPrices = $this->getPrices();
                $this->categoryDescription = sprintf("%s - %s", $category->getName(), $categoryPrices['price_low']);
            }
        }
        return $this->categoryDescription;
    }

    /*
     * Get category image url
     *
     * @return string
     */
    protected function getImage()
    {
        $category = $this->getCategory();

        return ($imgUrl = $category->getImageUrl()) ? $imgUrl : '';
    }

    /*
     * Get category offers
     *
     * @return array
     */
    protected function getPrices()
    {
        $offers = array(
            'price_low'     =>  $this->pricingHelper->currency('0.00', true, false),
            'price_high'    =>  $this->pricingHelper->currency('0.00', true, false),
            'clean_low'     =>  '0.00',
            'clean_high'    =>  '0.00',
            'currency'      =>  $this->storeManager->getWebsite()->getBaseCurrencyCode(),
            'qty'           =>  0,
        );
        if(!$category = $this->getCategory()) {
            return $offers;
        }
        $productCollection = $this->getCategoryProductCollection();
        if ($offers['qty'] = $productCollection->count()) {
            $price_low = $productCollection->getMinPrice();
            $offers['price_low'] = $this->pricingHelper->currency($price_low, true, false);
            $offers['clean_low'] = number_format($price_low, 2, '.', '');

            $price_high = $productCollection->getMaxPrice();
            $offers['price_high'] = $this->pricingHelper->currency($price_high, true, false);
            $offers['clean_high'] = number_format($price_high, 2, '.', '');
        }
        return $offers;
    }

    protected function getCategoryProductCollection()
    {
        if (is_null($this->categoryProductCollection)) {
            $this->categoryProductCollection = $this->productCollectionFactory->create();
            if(!$category = $this->getCategory()) {
                return $this->categoryProductCollection;
            }
            $this->categoryProductCollection->applyFrontendPriceLimitations();
            $this->categoryProductCollection->addCategoriesFilter(array('eq' => $category->getAllChildren(true)));
            $this->categoryProductCollection->addAttributeToFilter('visibility', array('eq' => ProductVisibility::VISIBILITY_BOTH))
                ->addAttributeToFilter('status', array('eq' => ProductStatus::STATUS_ENABLED))
            ;
        }
        return $this->categoryProductCollection;
    }

    /*
     * Get category ratings
     *
     * @return array
     */
    protected function getRatings()
    {
        $ratings = array(
            'best'      =>  100,
            'avg'       =>  0,
            'count'     =>  0,
            'percentage'=>  0,
            'type'      =>  'reviewCount'
        );
        if (!$this->getConfigurationValue('category/reviews')) {
            return $ratings;
        }
        if(!$category = $this->getCategory()) {
            return $ratings;
        }
        $productIds = $this->getCategoryProductCollection()->getAllIds();
        $summaryCollection = $this->summaryCollectionFactory->create();
        $summaryCollection->addEntityFilter($productIds)
            ->addStoreFilter($this->storeManager->getStore()->getId())
        ;

        $totals = array();
        $count = 0;
        foreach ($summaryCollection as $rating) {
            if(($rating->getReviewsCount() > 0) && ($rating->getRatingSummary() > 0)) {
                $totals[] = $rating->getRatingSummary();
                $count = ($count + $rating->getReviewsCount() );
            }
        }
        $ratings['count'] = $count;
        if(count($totals) > 0) {
            $ratings['percentage'] = (array_sum($totals) / count($totals));
            $ratings['avg'] = round($ratings['percentage']);
        }
        return $ratings;
    }

    /**
     * Returns array of some  OG category meta tags and twitter cards
     *
     * @return bool
     */
    public function generateCategoryMetatags()
    {
        $categoryTags = array();
        if (!$category = $this->getCategory())  {
            return $categoryTags;
        }
        if (!$this->getConfigurationValue('category/enabled')) {
            return $categoryTags;
        }
        if ($this->getConfigurationValue('category/opengraph')) {
            $categoryTags = array_merge($categoryTags, $this->getOpenGraphTags());
        }
        if ($this->getConfigurationValue('category/twitter')) {
            $categoryTags = array_merge($categoryTags, $this->getTwitterCards());
        }

        return $categoryTags;
    }

    public function getOpenGraphTags()
    {
        $snippets = $this->getCategorySnippets();
        /* Represent category as product since we have no specific meta tags for products */
        $categoryTags = [
            'og:type'           =>  'product',
            'og:url'            =>  $snippets['url'],
            'og:title'          =>  $snippets['title'],
            'og:description'    =>  $snippets['description'],
            'og:image:url'      =>  $snippets['image'],
            'og:availability'   => 'instock',
        ];

        /* OG image URL is mandatory, we need to make sure it is not empty */
        if (empty($categoryTags['og:image:url'])) {
            $openGraphLogo = $this->getConfigurationValue('cms/opengraph_logo');
            $openGraphLogoURL = $this->getConfigurationValue('cms/opengraph_logo_url');
            if ($openGraphLogo && !empty($openGraphLogoURL)) {
                $categoryTags['og:image:url'] = $openGraphLogoURL;
            } else {
                $categoryTags['og:image:url'] = $this->getImageURLFromFirstItem();
            }
        }

        $prices = $snippets['prices'];
        if (isset($prices['clean_low'])) {
            $categoryTags['og:price:amount']   = $prices['clean_low'];
            $categoryTags['og:price:currency'] = $prices['currency'];
        }

        $ratings = $snippets['ratings'];
        if (isset($ratings['count'])) {
            $categoryTags['og:rating']       = $ratings['avg'];
            $categoryTags['og:rating_scale'] = $ratings['best'];
            $categoryTags['og:rating_count'] = $ratings['count'];
        }

        $siteName = $this->getConfigurationValue('general/site_name');
        if (!empty($siteName)) {
            $categoryTags['og:site_name'] = $this->getConfigurationValue('general/site_name');
        }

        return $categoryTags;
    }

    /*
     * Retrieves image url from firs product
     *
     * @return string
     */
    public function getImageURLFromFirstItem()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->getCategoryProductCollection()->getFirstItem();
        $product = $product->load($product->getId());
        $image = $this->productImageHelper->init($product,'snippet_image_id');
        $imageUrl = $image->setImageFile($product->getImage())->getUrl();

        return $imageUrl;
    }

    /**
     * Generates twitter cards meta tags for category
     *
     * @param $category \Magento\Catalog\Model\Category
     * @param string
     * @return array
     */
    public function getTwitterCards()
    {
        if (!$twitterUsername = $this->getConfigurationValue('general/twitter_username')) {
            return [];
        }

        $snippets = $this->getCategorySnippets();
        $twitterCards = [
            'twitter:card'          =>  'product',
            'twitter:title'         =>  $snippets['title'],
            'twitter:url'           =>  $snippets['url'],
            'twitter:image:src'     =>  $snippets['image'],
            'twitter:site'          =>  $twitterUsername,
            'twitter:creator'       =>  $twitterUsername,
            'twitter:description'   =>  $snippets['description'],
            'twitter:label1'        =>  'PRICE',
            'twitter:data1'         =>  $snippets['prices']['price_low'],
            'twitter:data2'         =>  'In Stock',
            'twitter:label2'        =>  'AVAILABILITY'
        ];

        return $twitterCards;
    }

    /**
     * Generates JSON snippets for category pages
     *
     * @return string
     */
    public function generateCategoryJSON()
    {
        if (!($category = $this->getCategory()) ||
            !$this->getConfigurationValue('category/enabled') ||
            $this->getConfigurationValue('category/type') != 'json' ) {
            return '';
        }
        $snippets = $this->getCategorySnippets();

        $jsonTags['@context'] = 'http://schema.org';
        $jsonTags['@type'] = 'Product';
        $jsonTags['name'] = $snippets['name'];

        $description = $snippets['description'];
        if (!empty($description)) {
            $jsonTags['description'] = $description;
        }
        $image = $snippets['image'];
        if (!$image) {
            $image = $this->getImageURLFromFirstItem();
        }
        if (!empty($image)) {
            $jsonTags['image'] = $image;
        }

        $price = $snippets['prices'];

        if (isset($price['price_low'])) {
            $jsonTags['offers'] = array();
            $jsonTags['offers']['@type'] = 'AggregateOffer';
            $jsonTags['offers']['availability'] = ProductHelper::AVAILABILITY_IN_STOCK;

            if ($price['clean_high']) {
                $jsonTags['offers']['lowprice'] = $price['clean_low'];
                $jsonTags['offers']['highprice'] = $price['clean_high'];
            } else {
                $jsonTags['offers']['lowprice'] = $price['clean_low'];
            }

            $jsonTags['offers']['priceCurrency'] = $price['currency'];
        }

        $rating = $snippets['ratings'];

        if ($rating['percentage'] > 0) {
            $jsonTags['aggregateRating'] = array();
            $jsonTags['aggregateRating']['@type'] = 'AggregateRating';
            $jsonTags['aggregateRating']['bestRating'] = $rating['best'];
            $jsonTags['aggregateRating']['ratingValue'] = $rating['avg'];
            $jsonTags['aggregateRating'][$rating['type']] = $rating['count'];
        }

        return json_encode($jsonTags);
    }

    /**
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategorySnippets()
    {
        $category = $this->getCategory();

        $title = $category->getName();
        $image = $this->getImage();
        $categoryPrices = $this->getPrices();
        $description = $this->getDescription();

        $snippets = [
            'url'           => $this->filterManager->stripTags($this->url->getCurrentUrl()),
            'title'         => $this->escaper->escapeHtml($this->filterManager->stripTags($title)),
            'name'          => $this->escaper->escapeHtml($this->filterManager->stripTags($title)),
            'image'         => $this->filterManager->stripTags($image),
            'description'   => $this->escaper->escapeHtml($this->filterManager->stripTags($description)),
            'prices'        => $categoryPrices,
            'ratings'       => $this->getRatings(),
        ];

        return $snippets;
    }
}