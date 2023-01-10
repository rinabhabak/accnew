<?php
namespace Int\ReviewSubmitGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Authorization\Model\UserContextInterface;


class ReviewSubmit implements ResolverInterface
{
    private $_customerReview;

    public function __construct(
        \Int\ReviewSubmitGraphQl\Model\DataProvider\ReviewSubmit $companyRegistration
    ) {
        $this->_customerReview = $companyRegistration;
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
    ){
        try {
            $customer_id = null;
            if (($context->getUserId()) && $context->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
                $customer_id = $context->getUserId();
            }
            
            if (!isset($args['input']['product_id']) || empty($args['input']['product_id'])) {
                throw new GraphQlInputException(__('"product_id" value should be specified'));
            }

            if (!isset($args['input']['ratings']) || empty($args['input']['ratings'])) {
                throw new GraphQlInputException(__('"ratings" value should be specified'));
            }

            if (!isset($args['input']['nickname']) || empty($args['input']['nickname'])) {
                throw new GraphQlInputException(__('"nickname" value should be specified'));
            }

            if (!isset($args['input']['title']) || empty($args['input']['title'])) {
                throw new GraphQlInputException(__('"title" value should be specified'));
            }

            if (!isset($args['input']['detail']) || empty($args['input']['detail'])) {
                throw new GraphQlInputException(__('"detail" value should be specified'));
            }

            $customerReviewData = [
                    "product_id" => $args['input']['product_id'],
                    "ratings" => $args['input']['ratings'],
                    "nickname" => $args['input']['nickname'],
                    "title" => $args['input']['title'],
                    "detail" => $args['input']['detail'],
                    "customer_id" => $customer_id
            ];

            $response = $this->_customerReview->register($customerReviewData);
            
            return $response;

        } catch (AuthenticationException $e) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/reviewSubmit.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($e);
        }

    }
}