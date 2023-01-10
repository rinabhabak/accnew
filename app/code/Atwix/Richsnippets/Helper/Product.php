<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Helper;

use Atwix\Richsnippets\Helper\Data as SnippetsHelper;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Url as Url;
use Magento\Review\Model\Review\SummaryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Atwix\Richsnippets\Service\SerializerService;

/**
 * Class Product
 */
class Product extends SnippetsHelper
{
    const AVAILABILITY_IN_STOCK  = 'http://schema.org/InStock';
    const AVAILABILITY_OUT_STOCK = 'http://schema.org/OutOfStock';
    const NOT_SELECTED_IMAGE     = 'no_selection';
    const IMAGE_TYPES            = 'image,snippet_image';
    
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
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var SummaryFactory
     */
    protected $summaryFactory;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var ImageHelper
     */
    protected $productImageHelper;

    /**
     * Product constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param FilterManager $filterManager
     * @param PricingHelper $pricingHelper
     * @param Url $url
     * @param SummaryFactory $summaryFactory
     * @param ImageHelper $productImageHelper
     * @param Escaper $escaper
     * @param SerializerService $serializerService
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Registry $registry,
        FilterManager $filterManager,
        PricingHelper $pricingHelper,
        Url $url,
        SummaryFactory $summaryFactory,
        ImageHelper $productImageHelper,
        Escaper $escaper,
        SerializerService $serializerService
    ) {
        parent::__construct($context, $storeManager, $url, $serializerService);
        $this->registry = $registry;
        $this->filterManager = $filterManager;
        $this->pricingHelper = $pricingHelper;
        $this->summaryFactory = $summaryFactory;
        $this->escaper = $escaper;
        $this->productImageHelper = $productImageHelper;
    }

    /**
     * Returns array of some additional OG product meta tags and twitter cards
     *
     * @return array
     * @throws LocalizedException
     */
    public function generateProductMetatags()
    {
        $productTags = array();
        if (!$this->isSnippetEnabled('products')) {
            return $productTags;
        }

        if ($this->getConfigurationValue('products/rating')) {
            $productTags = array_merge($productTags, $this->getProductRatingsTags());
        }
        if ($this->getConfigurationValue('products/twitter')) {
            $productTags = array_merge($productTags, $this->getTwitterCards());
        }
        return $productTags;
    }

    /**
     * Generates rating tags for product
     *
     * @return array
     * @throws LocalizedException
     */
    public function getProductRatingsTags()
    {
        $tags = array();
        $productRatings = $this->getRatings();
        if (isset($productRatings['count'])) {
            $tags['og:rating'] = $productRatings['avg'];
            $tags['og:rating_scale'] = $productRatings['best'];
            $tags['og:rating_count'] = $productRatings['count'];
        }
        return $tags;
    }


    /**
     * Generates twitter cards meta tags for product
     *
     * @return array
     * @throws LocalizedException
     */
    public function getTwitterCards()
    {
        if (!$twitterUsername = $this->getConfigurationValue('general/twitter_username')) {
            return [];
        }

        $productSnippets = $this->getProductSnippets();
        $twitterCards = [
            'twitter:card'          =>  'product',
            'twitter:title'         =>  $productSnippets['title'],
            'twitter:url'           =>  $productSnippets['url'],
            'twitter:image:src'     =>  $productSnippets['image'],
            'twitter:site'          =>  $twitterUsername,
            'twitter:creator'       =>  $twitterUsername,
            'twitter:description'   =>  $productSnippets['description']
        ];

        $product = $this->getProduct();
        $productPriceValue = $product->getFinalPrice();
        $productPrice = $this->pricingHelper->currency($productPriceValue, true, false);

        $availability = $this->getAvailability($product);

        if (isset($availability['url'])) {
            if ($availability['url'] == self::AVAILABILITY_IN_STOCK) {
                $twitterCards['twitter:data2'] = 'In Stock';
                $twitterCards['twitter:label2'] = 'AVAILABILITY';
            }
        }

        $twitterCards['twitter:label1'] = 'PRICE';
        $twitterCards['twitter:data1'] = $productPrice;

        return $twitterCards;
    }

    /**
     * Returns currently viewed product
     *
     * @return ProductModel
     * @throws LocalizedException
     */
    public function getProduct()
    {
        if (!$product = $this->registry->registry('current_product')) {
            throw new LocalizedException(
                __('Make sure that Product exist.')
            );
        }
        return $product;
    }


    /**
     * Returns an array with product availability information
     *
     * @param $product \Magento\Catalog\Model\Product $product
     * @return array|bool
     */
    protected function getAvailability($product)
    {
        $availability = false;
        if ($this->getConfigurationValue('products/stock')) {
            $availability['text'] = ($product->isAvailable() ? __('In stock') : __('Out of Stock'));
            $availability['url'] = ($product->isAvailable() ?
                self::AVAILABILITY_IN_STOCK : self::AVAILABILITY_OUT_STOCK);
        }

        return $availability;
    }

    /**
     * Returns an array with product ratings information
     *
     * @return array|bool
     * @throws LocalizedException
     */
    protected function getRatings()
    {
        $product = $this->getProduct();

        if ($this->getConfigurationValue('products/rating')) {
            /** @var \Magento\Review\Model\Review\Summary $summaryModel */
            $summaryModel = $this->summaryFactory->create();
            /** @var \Magento\Review\Model\Review\Summary $summary */
            $summary = $summaryModel->setStoreId($this->storeManager->getStore()->getId())->load($product->getId());

            if (!$summary->getReviewsCount() > 0) {
                return false;
            }

            $ratings = [
                'best' => '100',
                'avg' => round($summary->getRatingSummary()),
                'count' => $summary->getReviewsCount(),
                'percentage' => $summary->getRatingSummary()
            ];

            $ratings['type'] = 'reviewCount';

            return $ratings;
        }

        return false;
    }

    /**
     * Retrieves data for product snippets
     *
     * @return array
     * @throws LocalizedException
     */
    public function getProductSnippets()
    {
        if (!$this->isSnippetEnabled('products')) {
            return [];
        }

        $product = $this->getProduct();

        $title = $product->getSnippetTitle();
        if (!$title) {
            $title = $product->getMetaTitle();
        }
        if (!$title) {
            $title = $product->getName();
        }

        $productPriceValue = $product->getFinalPrice();
        $productPrice = $this->pricingHelper->currency($productPriceValue, true, false);

        $description = $product->getSnippetDescription();
        if (!$description) {
            $description = $product->getMetaDescription();
        }
        if (!$description) {
            $description = $product->getShortDescription();
        }
        if (!$description) {
            $description = $product->getDescription();
        }
        if (!$description) {
            $description = sprintf("%s - %s", $title, $productPrice);
        }

        $image = $this->productImageHelper->init($product, 'product_page_image_large', explode(',', self::IMAGE_TYPES));
        if (!$product->getSnippetImage() || $product->getSnippetImage() == self::NOT_SELECTED_IMAGE) {
            $imageUrl = $image->setImageFile($product->getImage())->getUrl();
        } else {
            $imageUrl = $image->setImageFile($product->getSnippetImage())->getUrl();
        }

        $snippets = [
            'sku'           => $this->escaper->escapeHtml($this->filterManager->stripTags($product->getSku())),
            'url'           => $this->escaper->escapeHtml($this->filterManager->stripTags($product->getUrlModel()->getUrl($product, ['_ignore_category' => true]))),
            'title'         => $this->escaper->escapeHtml($this->filterManager->stripTags($title)),
            'name'          => $this->escaper->escapeHtml($this->filterManager->stripTags($title)),
            'logo'          => $this->escaper->escapeHtml($this->filterManager->stripTags($imageUrl)),
            'image'         => $this->escaper->escapeHtml($this->filterManager->stripTags($imageUrl)),
            'description'   => $this->escaper->escapeHtml($this->filterManager->stripTags($description))
        ];

        return $snippets;
    }
}