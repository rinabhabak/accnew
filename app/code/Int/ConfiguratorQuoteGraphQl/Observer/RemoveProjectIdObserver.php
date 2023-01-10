<?php

namespace Int\ConfiguratorQuoteGraphQl\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class RemoveProjectIdObserver implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
    * Json Serializer
    *
    * @var JsonSerializer
    */
    protected $jsonSerializer;

    /**
     * Set payment fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function __construct(
        JsonSerializer $jsonSerializer,
        RequestInterface $request,
        Session $checkoutSession,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Item $itemResourceModel,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $itemRepository
    ) {
        $this->_request = $request;
        $this->jsonSerializer = $jsonSerializer;
        $this->_checkoutSession = $checkoutSession;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->itemResourceModel = $itemResourceModel;
        $this->quoteFactory = $quoteFactory;
        $this->itemRepository = $itemRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/remove.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("==============>");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quoteItems = $objectManager->create('\\Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');

        $quoteItem = $observer->getItem();
      
        
        $quote = $objectManager->create('\Magento\Quote\Model\Quote')->load($quoteItem->getQuoteId());
        

        $quoteItemsCollection = $quoteItems->create()->addFieldToFilter('configurator_pid', array('neq'=>''))
                                    ->addFieldToFilter('quote_id', $quote->getId());
        
        
        if (empty($quoteItemsCollection->getAllIds())){
            $quote->setConfiguratorPid('')->save();
        }else{
            if($quote->getConfiguratorPid()){
                $cartProjectId = $quote->getConfiguratorPid();
                $cartProjectIds = explode(',',$cartProjectId);
                //array_push($cartProjectIds, $projectId);
                $cartProjectIds = array_unique($cartProjectIds);
                //$cartProjectIds = implode(",",$cartProjectIds);
                
                $items = $quote->getAllVisibleItems();
                $_itemPids = array();
                foreach($items as $item){
                    $_itemPid = $item->getConfiguratorPid();
                    if($_itemPid){
                        $itemPids = explode(',', $_itemPid);
                        $_itemPids = array_merge($_itemPids, $itemPids);
                    }
                }
                
                
                $_itemPids = array_unique($_itemPids);
                
                $_cartProjectId = array_intersect($cartProjectIds, $_itemPids);
                $_cartProjectId = implode(",",$_cartProjectId);
                //echo $_cartProjectId;
                $quote->setConfiguratorPid($_cartProjectId)->save();
                
            }
        }
        

    }
}