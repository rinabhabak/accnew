<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */

namespace Atwix\Richsnippets\Plugin\Block\Product;

use Atwix\Richsnippets\Helper\Category as CategoryHelper;
use Magento\Review\Block\Product\ReviewRenderer as SubjectBlock;

/**
 * Class ReviewRenderer
 */
class ReviewRenderer
{
    const PRODUCT_SCHEMA  = 'http://schema.org/Product';

    /**
     * @var CategoryHelper
     */
    protected $helper;

    /**
     * Pages where the product reviews need to be fixed
     *
     * @var array
     */
    protected $pagesWithProductReviews = [
        'catalog_category_view',
        'cms_index_index',
        'catalogsearch_result_index',
    ];

    /**
     * ReviewRenderer constructor
     *
     * @param CategoryHelper $helper
     */
    public function __construct(
        CategoryHelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Add product schema for product reviews
     *
     * @param SubjectBlock $subject
     * @param string $result
     * @return string
     */
    public function afterGetReviewsSummaryHtml(SubjectBlock $subject, $result = '')
    {
        if (!$this->helper->isSnippetEnabled('products')) {
            return $result;
        }

        if ($result != ''
            && !is_null($subject->getRequest())
            && in_array($subject->getRequest()->getFullActionName(), $this->pagesWithProductReviews)
            && $this->helper->isProductReviewFixEnabled()
            && $product = $subject->getProduct()) {
            $result = '<div itemscope itemtype="' . self::PRODUCT_SCHEMA . '"><div itemprop="name" content="'
                . htmlspecialchars($product->getName()) . '"></div>' . $result . '</div>';
        }

        return $result;
    }
}
