<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_BannerGraphQl
 * @author    Indusnet
 */

namespace Int\CustomerGraphQl\Model\Resolver;


use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

/**
 * Class Banner
 * @package Int\RatingGraphQl\Model\Resolver
 */
class JobTitle implements ResolverInterface
{

    private $_ratingDataProvider;

    /**
     * @param \Int\RatingGraphQl\Model\Resolver\DataProvider\Rating $ratingDataProvider
     */
    public function __construct(
        \Int\CustomerGraphQl\Model\Resolver\DataProvider\JobTitle $ratingDataProvider
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

       
          /*
          echo json_encode($args['input']);
          die();
          */
        /*===========================================*/
        try {

        if (!isset($args['input']['customer_id']) || empty($args['input']['customer_id'])) {
            throw new GraphQlInputException(__('"customer_id" should be specified'));
        }

        if (!isset($args['input']['job_title']) || empty($args['input']['job_title'])) {
            throw new GraphQlInputException(__('"job_title"  should be specified'));
        }

         $pageData = [];

        
           
                $pageData = $this->_ratingDataProvider->updateCustomerJobTitle($args['input']);
         
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $pageData;

    }

}