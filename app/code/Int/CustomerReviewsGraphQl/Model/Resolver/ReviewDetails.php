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

class ReviewDetails implements ResolverInterface
{
    protected $timezone;
    protected $total_rating;
    protected $_reviewCollection; 
    protected $_storeManager;

    public function __construct(
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $voteFactory,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollection,
         \Magento\Store\Model\StoreManagerInterface $storemanager
    ){
        $this->_ratingFactory = $ratingFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_voteFactory = $voteFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_productFactory = $productFactory;
        $this->timezone = $timezone;
        $this->_reviewCollection = $reviewCollection;
        $this->_storeManager =  $storemanager;
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
        
        $reviewsData = $this->getReviewData((int) $context->getUserId(), (int) $args['id'], (int) $context->getExtensionAttributes()->getStore()->getId());

        return $reviewsData;
    }

    /**
     * @param int $review_id
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getReviewData(int $customer_id, int $review_id, int $store_id): array
    {
        if(!isset($review_id) || !isset($customer_id)){
            return null;
        }

        try {
            $returnData = [];

            $reviewData = $this->_reviewFactory->create()->load($review_id);
            
            $_product = $this->_productFactory->create()->load($reviewData->getEntityPkValue());
            
            $approvedReviewCollection = $this->_reviewCollection->create()->addStoreFilter(
            $this->_storeManager->getStore()->getId()
            )->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_APPROVED
                )->addEntityFilter(
                'product',
                $_product->getEntityId()
            );
            $reviewFactory = $this->_reviewFactory->create();
            $reviewFactory->getEntitySummary($_product, $this->_storeManager->getStore()->getStoreId());
            $ratingSummary = $_product->getRatingSummary()->getRatingSummary();
            $submitted = $this->timezone->date($reviewData->getCreatedAt())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            $store = $this->_storeManager->getStore();
            $media_url = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $returnData = [
                "product_name" => $_product->getName(),
                "product_image" => $media_url.'catalog/product'.$_product->getImage(),
                "product_url_key" => $_product->getUrlKey(),
                "product_description" => $_product->getDescription(),
                "review_title" => $reviewData->getTitle(),
                "review_detail" => $reviewData->getDetail(),
                "rating" => $this->getRating($review_id),
                "avg_rating" => $ratingSummary,
                "approved_review_count" => $approvedReviewCollection->getSize(),
                "submitted" => date('F j, Y',strtotime($submitted))
            ];
            
            return $returnData;

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        
    }

    /**
     * @param int $review_id
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getRating(int $review_id) : array
    {
        try {
            $collection = $this->_voteFactory->create()
                                ->addFieldToFilter('review_id', $review_id);
            
            foreach ($collection as $_item) {
                $itemsData[] = $_item->getData();
            }
            
            return $itemsData;

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    }
}
