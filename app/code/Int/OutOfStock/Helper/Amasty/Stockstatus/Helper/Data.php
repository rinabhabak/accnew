<?php
/**
 * @author Indusnet Team
 * @package Int_OutOfStock
 */

namespace Int\OutOfStock\Helper\Amasty\Stockstatus\Helper;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\ScopeInterface;
use Amasty\Stockstatus\Model\Source\BackOrder;
use Amasty\Stockstatus\Model\Backend\UpdaterAttribute;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ProductAlert\Block\Product\View;
use Magento\Framework\App\Action\Action;

class Data extends \Amasty\Stockstatus\Helper\Data
{
    /**
     * 1 Day is 24*60*60 = 86400sec;
     */
    const ONE_DAY = 86400;

    /**
     * @var array
     */
    private $cachedStatuses = null;

    /**
     * @var array
     */
    private $statusId = [];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var \Amasty\Stockstatus\Model\RangesFactory
     */
    private $rangesFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\OptionManagementFactory
     */
    private $optionManagementFactory;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\Cms\Helper\Page
     */
    private $pageHelper;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var null|string
     */
    private $infoBlock = null;

    /**
     * @var \Amasty\Stockstatus\Model\ResourceModel\Inventory
     */
    private $inventoryManager;
    private $blockFactory;
    private $config;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Amasty\Stockstatus\Helper\Image $imageHelper,
        \Amasty\Stockstatus\Model\RangesFactory $rangesFactory,
        \Magento\Catalog\Model\Product\Attribute\OptionManagementFactory $optionManagementFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Cms\Helper\Page $pageHelper,
        DateTime $dateTime,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Escaper $escaper,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Stockstatus\Model\ResourceModel\Inventory $inventoryManager,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Amasty\Xnotif\Helper\Config $config
    ) {
        parent::__construct($registry,$localeDate,$imageHelper,$rangesFactory,
                            $optionManagementFactory,$stockRegistry,$productRepository,
                            $context,$layout,$pageHelper,$dateTime,$priceCurrency,
                            $escaper,$pageFactory,$storeManager,$inventoryManager);
        $this->scopeConfig = $context->getScopeConfig();
        $this->localeDate = $localeDate;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->stockRegistry = $stockRegistry;
        $this->imageHelper = $imageHelper;
        $this->rangesFactory = $rangesFactory;
        $this->optionManagementFactory = $optionManagementFactory;
        $this->layout = $layout;
        $this->pageHelper = $pageHelper;
        $this->dateTime = $dateTime;
        $this->priceCurrency = $priceCurrency;
        $this->moduleManager = $context->getModuleManager();
        $this->escaper = $escaper;
        $this->pageFactory = $pageFactory;
        $this->storeManager = $storeManager;
        $this->inventoryManager = $inventoryManager;
        $this->blockFactory = $blockFactory;
        $this->config = $config;
    }

    
    /**
     * @param ProductInterface $product
     *
     * @return string
     */
    public function getStockAlert(Product $product)
    {
        if (!$product || !$product->getId() || !$this->config->allowForCurrentCustomerGroup('stock')) {
            return '';
        }

        $alertBlock =  $this->createDefaultAlertBlock();
        return $this->observeStockAlertBlock($product, $alertBlock);
    }

    /**
     * @param ProductInterface $product
     * @param View $alertBlock
     *
     * @return string
     */
    public function observeStockAlertBlock(Product $product, View $alertBlock)
    {
        $html = '';
        $currentProduct = $this->registry->registry('current_product');
        if (!$product->getId() || !$currentProduct) {
            return $html;
        }

        /*check if it is child product for replace product registered to child product.*/
        $isChildProduct = ($currentProduct->getId() != $product->getId());
        if ($isChildProduct) {
            $alertBlock->setData('parent_product_id', $currentProduct->getId());
            $alertBlock->setOriginalProduct($product);
        }
        $alertBlock->setSignupUrl($this->getSignupUrl(
            'stock',
            $product->getId(),
            $alertBlock->getData('parent_product_id')
        ));

        if ($alertBlock && !$product->getData('amxnotif_hide_alert')) {
            if (!$this->isLoggedIn()) {
                $alertBlock->setTemplate('Amasty_Xnotif::product/view_email.phtml');
            }

            $alertBlock->setData('amxnotif_observer_triggered', 1);
            $html = $alertBlock->toHtml();
            $alertBlock->setData('amxnotif_observer_triggered', null);
        }

        return $html;
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

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->config->isLoggedIn();
    }

   /**
     * @param $type
     * @param int $productId
     * @param null|int $parentId
     * @param bool $addUencInUrl
     *
     * @return string
     */
    public function getSignupUrl($type, $productId, $parentId = null, $addUencInUrl = true)
    {
        $params = ['product_id' => $productId];
        if ($addUencInUrl) {
            $params[Action::PARAM_NAME_URL_ENCODED] = $this->getEncodedUrl();
        }

        if ($parentId) {
            $params['parent_id'] = $parentId;
        }

        return $this->_getUrl('xnotif/email/' . $type, $params);
    }
    /**
     * @param string $url
     * @return string
     */
    public function getEncodedUrl($url = null)
    {
        if (!$url) {
            $url = $this->_urlBuilder->getCurrentUrl();
        }

        return $this->urlEncoder->encode($url);
    }
}
