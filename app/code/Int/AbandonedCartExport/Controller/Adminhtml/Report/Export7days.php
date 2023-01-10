<?php
/**
 * Copyright © Indusnet Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\AbandonedCartExport\Controller\Adminhtml\Report;

use Magento\Framework\App\Filesystem\DirectoryList;

class Export7days extends \Magento\Backend\App\Action
{

    protected $_quoteCollection;
    protected $_quoteItemCollection;
    protected $_fileFactory;
    protected $_filesystem;
    protected $_quoteEmail;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Amasty\Acart\Model\QuoteEmail $quoteEmail
    ) {
        $this->_quoteCollection = $quoteCollectionFactory;
        $this->_quoteItemCollection = $quoteItemFactory;
        $this->_fileFactory = $fileFactory;
        $this->_quoteEmail = $quoteEmail;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        //die('ghfh');
        $toDate = date('Y-m-d 23:59:59', time());
        $fromDate = date('Y-m-d 00:00:00', strtotime('-7 day'));

        $headers = [
            'Customer Name',
            'Customer Email',
            'Product Name',
            'Product SKU',
            'Total Item Count',
            'Quantity',
            'Subtotal',
            'Applied Coupon',
            'Created Date', 
            'IP Address'
        ];
        
        $filepath = 'export/abandoned_cart' . strtotime("now") . '.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $stream->writeCsv($headers);

        $quoteCollection = $this->_quoteCollection->create()->addFieldToSelect('*')
                        ->addFieldToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
                        ->addFieldToFilter('items_count', ['gt' => 0]);

        $quotedetails = array();
        $quoteItemCollectionLoad = $this->_quoteItemCollection->create();
        foreach($quoteCollection as $quote)
        {

            if(!$quote->getId()){
                continue;
            }

            $products = [];

            $quoteItems = $quoteItemCollectionLoad->addFieldToFilter('quote_id',$quote->getId())
                        ->addFieldToFilter('row_total',['neq' => 0]);

            foreach($quoteItems as $quoteItem){
                $products['name'][] = $quoteItem->getName();
                $products['sku'][] = $quoteItem->getSku();
            }
            $quoteItems = array();
            if(empty($products['name']) && empty($products['sku'])){
                $products['name'][] = 'N/A';
                $products['sku'][] = 'N/A';
            }

            $customer_email = $quote->getCustomerEmail();

            if(empty($customer_email) || is_null($customer_email)){
                $quoteEmail = $this->_quoteEmail->load($quote->getId(), 'quote_id');
                $customer_email = $quoteEmail->getCustomerEmail();
            }

            foreach($products['name'] as $key => $value)
            {
                if(empty($customer_email)){
                    continue;
                }
                
            	$quotedetails['customer_name'] = $quote->getCustomerFirstname() .' '.$quote->getCustomerLastname();
	            $quotedetails['email'] = $customer_email;
	            $quotedetails['product_name'] = $value;
	            $quotedetails['product_sku'] = $products['sku'][$key];
	            $quotedetails['item_count'] = $quote->getItemsCount();
	            $quotedetails['quantity'] = $quote->getItemsQty();
	            $quotedetails['subtotal'] = $quote->getSubtotal();
	            $quotedetails['coupon_code'] = $quote->getCouponCode();
	            $quotedetails['created_at'] = $quote->getCreatedAt();
	            $quotedetails['remote_ip'] = $quote->getRemoteIp();

	            $stream->writeCsv($quotedetails);
            }
            
        }
                        
        $content = [];
        $content['type'] = 'filename';
        $content['value'] = $filepath;
        $content['rm'] = '1';

        $csvfilename = 'abandoned_cart'. strtotime("now") .'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
    }
}

