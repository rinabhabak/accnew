<?php
namespace Int\RatingGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

class Rating
{
 
    protected $_review; 

    protected $_productloader;  

    protected $_storeManager; 

    public function __construct(

    \Magento\Review\Model\ReviewFactory $Review,
    \Magento\Catalog\Model\ProductFactory $_productloader,
    \Magento\Store\Model\StoreManagerInterface $StoreManager

    
    ) {
    
    $this->_review = $Review;
    $this->_productloader = $_productloader;
    $this->_storeManager = $StoreManager;
 
    }
    
    
    /**
     * Get banner data
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    public function getProductRatingById($product_id)
    {
        try {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/graphql.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($product_id);

            $products = explode (",", $product_id);  
            foreach($products as $productId){
                $reviewFactory = $this->_review->create();
                $product = $this->_productloader->create()->load($productId);
                $storeId = $this->_storeManager->getStore()->getStoreId();
                $reviewFactory->getEntitySummary($product, $storeId);

                $ratingSummary = $product->getRatingSummary()->getRatingSummary();
                $reviewCount = $product->getRatingSummary()->getReviewsCount();
                $resultData[]  = ['product_id'=> (int)$product->getId(), 'ratingSummary'=>$ratingSummary, 'reviewCount' => $reviewCount];
            }

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $resultData;
    }
}
