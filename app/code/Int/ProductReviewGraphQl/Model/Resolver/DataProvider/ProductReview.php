<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ProductReviewGraphQl
 * @author    Indusnet
 */
 
namespace Int\ProductReviewGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductReview
{
    protected $productRepository; 
    protected $_reviewCollection; 

    public function __construct(
       \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
      \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollection
        )
    {
        $this->productRepository = $productRepository;
        $this->_reviewCollection = $reviewCollection;
    }
    
    
    /**
     * Get ProductReview data
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    public function getproductReviewData($productId)
    {
        try {

            
            $product = $this->productRepository->getById($productId);
            $collection = $this->_reviewCollection->create()
            ->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_APPROVED
                )->addEntityFilter(
                'product',
                    $productId
                )->setDateOrder()->addRateVotes();
                $reviewarray =  array();
                $i = 0;
             foreach ($collection as $_review) {

              $reviewarray[$i]['review_id']= $_review->getreview_id();
              $reviewarray[$i]['detail']= $_review->getDetail();
              $reviewarray[$i]['created_at']= $newDate = date("M j, Y", strtotime($_review->getcreated_at()));
              $reviewarray[$i]['status_id']= $_review->getstatus_id();
              $reviewarray[$i]['title']= $_review->gettitle();
              $reviewarray[$i]['nickname']= $_review->getnickname();
              $reviewarray[$i]['customer_id']= $_review->getcustomer_id();
              $reviewarray[$i]['entity_code']= $_review->getentity_code();
              $_votes = $_review->getRatingVotes();
              $reviewarray[$i]['rating'] = [];
            if (count($_votes)) {
              $j = 0;
                foreach ($_votes as $_vote) {
                  
                    $reviewarray[$i]['rating'][$j]['rating_code']= $_vote->getRatingCode();
                    $reviewarray[$i]['rating'][$j]['rating_percent']  = $_vote->getPercent();
                    $j++;

                }
            }
            $i++;
        }
            
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $reviewarray;
    }
}
