<?php
namespace Int\ReviewSubmitGraphQl\Model\DataProvider;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class ReviewSubmit
{
    private $_reviewFactory;
    private $_ratingFactory;
    private $_storeManager;


    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_storeManager = $storeManager;
    }

    public function register($data){

        $message = [];
        try {
        $reviewFinalData = [];
        $productId = $data['product_id'];//product id you set accordingly
        $reviewFinalData['ratings'][1] = $data['ratings']['rating_quality'];
        $reviewFinalData['ratings'][2] = $data['ratings']['rating_value'];
        $reviewFinalData['ratings'][3] = $data['ratings']['rating_price'];
        $reviewFinalData['nickname'] = $data['nickname'];
        $reviewFinalData['title'] = $data['title'];
        $reviewFinalData['detail'] = $data['detail'];
        $review = $this->_reviewFactory->create()->setData($reviewFinalData);
        $review->unsetData('review_id');
        $review->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
            ->setEntityPkValue($productId)
            ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)//By default set approved
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->setStores([$this->_storeManager->getStore()->getId()])
            ->setCustomerId($data['customer_id'])
            ->save();

        foreach ($reviewFinalData['ratings'] as $ratingId => $optionId) {
            $this->_ratingFactory->create()
                ->setRatingId($ratingId)
                ->setReviewId($review->getId())
                ->addOptionVote($optionId, $productId);
        }

        $review->aggregate();

            $message['message'] = "You submitted your review for moderation.";
            
        } catch (\Exception $e) { 
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/reviewSubmit.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($e);
        }
        
        return $message;
    }

}
