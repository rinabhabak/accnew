<?php
namespace Int\HomeBanner\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $_locationFactory;

    public function __construct(
        \Amasty\Storelocator\Model\ResourceModel\Location\Collection $locationCollection,
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->_locationFactory  = $locationCollection;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $locationCollection = $objectManager->get('Amasty\Storelocator\Model\Location')->getCollection();
        $locationCollection->applyDefaultFilters();
        $locationCollection->load();

        echo "<pre>";
        print_r($locationCollection->getData());
    }
}