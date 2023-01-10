<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ProductReviewGraphQl
 * @author    Indusnet
 */

namespace Int\ProductReviewGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ProductReview
 * @package Int\ProductReviewGraphQl\Model\Resolver
 */
class ProductReview implements ResolverInterface
{

    private $_productReviewGraphQlDataProvider;

    /**
     * @param Int\ProductReviewGraphQl\Model\Resolver\DataProvider\ProductReview $productReviewGraphQlDataProvider
     */
    public function __construct(
        \Int\ProductReviewGraphQl\Model\Resolver\DataProvider\ProductReview $productReviewGraphQlDataProvider
    ) {
        $this->_productReviewGraphQlDataProvider = $productReviewGraphQlDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        $resultData =  $this->_productReviewGraphQlDataProvider->getproductReviewData($args['input']['productId']);
        return $resultData;
    }

}