<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_AggregationGraphQl
 * @author    Indusnet
 */
namespace Int\AggregationGraphQl\Model\Resolver\DataProvider;

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
     * Get Rating data
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    public function getProductRatingById($entity_id)
    {
        try {

            $reviewFactory = $this->_review->create();
            $product = $this->_productloader->create()->load($entity_id);
            $storeId = $this->_storeManager->getStore()->getStoreId();
            $reviewFactory->getEntitySummary($product, $storeId);

            $ratingSummary = $product->getRatingSummary()->getRatingSummary();
            
            

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $ratingSummary;
    }
}
