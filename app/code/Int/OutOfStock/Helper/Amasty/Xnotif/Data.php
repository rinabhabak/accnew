<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Int\OutOfStock\Helper\Amasty\Xnotif;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Action\Action;
use Magento\ProductAlert\Block\Product\View;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

/**
 * Class Data
 */
class Data extends \Amasty\Xnotif\Helper\Data
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    private $blockFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\App\Helper\Context $context,
        \Amasty\Xnotif\Helper\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        
        $this->registry = $registry;
        $this->blockFactory = $blockFactory;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->config = $config;
        parent::__construct($registry,$blockFactory,$stockRegistry,$context,$config,$storeManager);
        
    }


    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function createDefaultAlertBlock()
    {
        $alertBlock = $this->blockFactory->createBlock(
            \Magento\ProductAlert\Block\Product\View::class,
            []
        );

        $alertBlock->setTemplate('Magento_ProductAlert::product/view.phtml');
        $alertBlock->setHtmlClass('alert stock link-stock-alert');
        $alertBlock->setSignupLabel(__('Notify me when this product is back in stock.'));

        return $alertBlock;
    }

   
}
