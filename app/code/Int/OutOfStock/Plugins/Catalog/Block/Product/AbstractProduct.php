<?php
/**
 * @author Indusnet Team
 * @package Int_OutOfStock
 */


namespace Int\OutOfStock\Plugins\Catalog\Block\Product;

use Magento\Catalog\Block\Product\AbstractProduct as ProductBlock;
use Magento\Catalog\Model\Product as ProductModel;
use Amasty\Xnotif\Block\Catalog\Category\StockSubscribe;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableModel;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Class AbstractProduct
 */
class AbstractProduct extends \Amasty\Xnotif\Plugins\Catalog\Block\Product\AbstractProduct
{
    const CATEGORY_BLOCK_NAME = 'category.subscribe.block';

    /**
     * @var string
     */
    private $loggedTemplate;

    /**
     * @var string
     */
    private $notLoggedTemplate;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $xnotifHelper;

    /**
     * @var ProductModel|null
     */
    private $product;

    /**
     * @var array
     */
    private $processedProducts = [];

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Xnotif\Helper\Config
     */
    private $config;

    public function __construct(
        \Amasty\Xnotif\Helper\Data $xnotifHelper,
        \Amasty\Xnotif\Helper\Config $config,
        \Magento\Framework\Registry $registry
    ) {
        $this->loggedTemplate = 'Magento_ProductAlert::product/view.phtml';
        $this->notLoggedTemplate = 'Amasty_Xnotif::category/subscribe.phtml';
        $this->xnotifHelper = $xnotifHelper;
        $this->registry = $registry;
        $this->config = $config;
        parent::__construct($xnotifHelper,$config,$registry);
    }

    

    /**
     * @param BlockInterface $subscribeBlock
     * @param $product
     * @param $addUencInUrl
     */
    protected function prepareSubscribeBlock(BlockInterface $subscribeBlock, $product, $addUencInUrl)
    {
        if ($this->xnotifHelper->isLoggedIn()) {
            $subscribeBlock->setTemplate($this->loggedTemplate);
            $subscribeBlock->setHtmlClass('alert stock link-stock-alert');
            $subscribeBlock->setSignupLabel(
                __('Notify me when back in stock')
            );
            $subscribeBlock->setSignupUrl(
                $this->xnotifHelper->getSignupUrl(
                    'stock',
                    $product->getId(),
                    $subscribeBlock->getData('parent_product_id'),
                    $addUencInUrl
                )
            );
        } else {
            $subscribeBlock->setTemplate($this->notLoggedTemplate);
            $subscribeBlock->setOriginalProduct($product);
        }
    }

    
}
