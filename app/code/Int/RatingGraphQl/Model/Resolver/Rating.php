<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_BannerGraphQl
 * @author    Indusnet
 */

namespace Int\RatingGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Banner
 * @package Int\RatingGraphQl\Model\Resolver
 */
class Rating implements ResolverInterface
{

    private $_ratingDataProvider;

    /**
     * @param \Int\RatingGraphQl\Model\Resolver\DataProvider\Rating $ratingDataProvider
     */
    public function __construct(
        \Int\RatingGraphQl\Model\Resolver\DataProvider\Rating $ratingDataProvider
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

       
        /*  $resultData =  $this->_ratingDataProvider->getBannerData($args['product_id']);
            return $resultData; */
        /*===========================================*/
        if (!isset($args['product_ids'])) {
            throw new GraphQlInputException(__('Product id should be specified'));
        }

         $pageData = [];

        try {
            if (isset($args['product_ids'])) {
                $pageData = $this->_ratingDataProvider->getProductRatingById($args['product_ids']);
            } 

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $pageData;

    }

}