<?php

namespace Int\ConfiguratorQuoteGraphQl\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;

class CollectTotals implements ObserverInterface
{
    
    protected $_quoteItemCollection;
   
    public function __construct(
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollection
    ){
        $this->_quoteItemCollection = $quoteItemCollection;
    }

    /**
     * @event sales_quote_collect_totals_before
     * @event sales_quote_address_collect_totals_before
     * @param Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $quoteItemsCollection = $this->_quoteItemCollection->create()
                        ->addFieldToFilter('configurator_pid', array('neq'=>''))
                        ->addFieldToFilter('quote_id', array('eq'=>$quote->getId()));
                        
        if (empty($quoteItemsCollection->getAllIds())){
            $quote->setConfiguratorPid(NULL);
            $quote->save();
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
                $quote->setConfiguratorPid($_cartProjectId)->save();
                
            }
        }
        
         
    }

}