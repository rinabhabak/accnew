<?php
declare(strict_types=1);

namespace Int\CustomerReviewsGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class CustomerReviews implements ResolverInterface
{
    protected $timezone;
    protected $productRepository; 

    public function __construct(
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $voteFactory,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        ProductRepositoryInterface $productRepository
    ){
        $this->_ratingFactory = $ratingFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_voteFactory = $voteFactory;
        $this->timezone = $timezone;
        $this->productRepository = $productRepository;
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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $reviewsData = $this->getReviewsData($context->getUserId(), $context->getExtensionAttributes()->getStore()->getId());

        return $reviewsData;
    }

    /**
     * @param int $customer_id
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getReviewsData($customer_id, $store_id): array
    {
        try {
            $returnData = [];

            $reviewsCollection = $this->_collectionFactory->create()
                ->addStoreFilter($store_id)
                ->addCustomerFilter($customer_id)
                ->setDateOrder();
            foreach($reviewsCollection as $review){
                $product = $this->productRepository->getById($review->getEntityId());
                $reviewCreatedDate = $this->timezone->date($review->getReviewCreatedAt())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                $returnData[] = [
                    "product_name" => $review->getName(),
                    "product_url" => $product->getUrlKey(),
                    "review_id" => $review->getReviewId(),
                    "rating" => $this->getRating((int) $review->getReviewId()),
                    "review" => $review->getDetail(),
                    "created" => date('n/j/y',strtotime($reviewCreatedDate))
                ];
            }

            return $returnData;

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        
    }

    /**
     * @param int $review_id
     * @return float
     * @throws GraphQlNoSuchEntityException
     */
    private function getRating(int $review_id) : float
    {
        try {
            $collection = $this->_voteFactory->create()
                                ->addFieldToSelect('review_id')
                                ->addFieldToFilter('review_id', $review_id);
            
            $collection->getSelect()->columns([
                'total' => new \Zend_Db_Expr('SUM(percent)'),
                'count' => new \Zend_Db_Expr('COUNT(percent)')
            ])->group('review_id');

            $ratingDetails = $collection->getData();

            return ((int) $ratingDetails[0]['total'] / (int)$ratingDetails[0]['count']);

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    }
}
