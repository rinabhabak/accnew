<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_AggregationGraphQl
 * @author    Indusnet
 */

namespace Int\AggregationGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class Rating
 * @package Int\AggregationGraphQl\Model\Resolver
 */
class Rating implements ResolverInterface
{


    private $_ratingDataProvider;

    /**
     * @param \Int\AggregationGraphQl\Model\Resolver\DataProvider\Rating $ratingDataProvider
     */
    public function __construct(
        \Int\AggregationGraphQl\Model\Resolver\DataProvider\Rating $ratingDataProvider
    ) {
        $this->_ratingDataProvider = $ratingDataProvider;
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
        $product = $value['model'];
        $entity_id = $value['entity_id'];
        $_ratingData = $this->_ratingDataProvider->getProductRatingById($entity_id);

        return $_ratingData;

    }

}