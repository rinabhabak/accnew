<?php
/**
 * @author Indusnet Team
 * @package Int_OutOfStock
 */

namespace Int\OutOfStock\Plugins\ConfigurableProduct;
use \Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;


/**
 * Class Data
 */
class Data extends \Amasty\Xnotif\Plugins\ConfigurableProduct\Data
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var StockStateInterface 
     */
    protected $stockItem;

    /**
     * @var \Amasty\Stockstatus\Helper\Data
     */
    private $stockStatusHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Amasty\Xnotif\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        StockStateInterface $stockItem,
        StockRegistryInterface $stockRegistry,
        \Amasty\Stockstatus\Helper\Data $stockStatusHelper

    ) {
        $this->imageHelper = $imageHelper;
        $this->moduleManager = $moduleManager;
        $this->helper = $helper;
        $this->registry = $registry;
        $this->stockItem = $stockItem;
        $this->stockRegistry = $stockRegistry;
        $this->stockStatusHelper = $stockStatusHelper;
        parent::__construct($imageHelper,$helper,$registry,$moduleManager);
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param \Magento\Catalog\Model\Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $aStockStatus = [];
        $allowAttributes = $this->getAllowAttributes($currentProduct);
        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            $productStock = $this->stockItem->getStockQty($productId, $product->getStore()->getWebsiteId());
            //print_r($this->stockItem->getBackorders($productId, $product->getStore()->getWebsiteId()));
            $stockBackorderItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
            $customStockStatus = $this->stockStatusHelper->getCustomStockStatusText($product);
            $key = [];
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                $options[$productAttributeId][$attributeValue][] = $productId;
                $options['index'][$productId][$productAttributeId] = $attributeValue;
                /* Int OutOfStock override Amasty\Xnotif\Plugins\ConfigurableProduct\Data start */
                $options['count'][$productAttributeId][$attributeValue][$productId] = $productStock;
                $options['count'][$productAttributeId][$attributeValue]["backorder"] = $stockBackorderItem->getBackorders();
                $options['count'][$productAttributeId][$attributeValue]["isInStock"] = $stockBackorderItem->getIsInStock();
                $options['count'][$productAttributeId][$attributeValue]["customStockStatus"] = $customStockStatus;
                /* Int OutOfStock override Amasty\Xnotif\Plugins\ConfigurableProduct\Data end */
                $key[] = $attributeValue;
            }
            

            if ($key && !$this->moduleManager->isEnabled('Amasty_Stockstatus')) {

                $saleable =  $this->helper->isItemSalable($product);

                $aStockStatus[implode(',', $key)] = [
                    'is_in_stock'   => $saleable,
                    'custom_status' => (!$saleable) ? __('Out of Stock') : '',
                    'product_id'    => $product->getId()
                ];
                if (!$saleable) {
                    $aStockStatus[implode(',', $key)]['stockalert'] =
                        $this->helper->getStockAlert($product);
                }

                $aStockStatus[implode(',', $key)]['pricealert'] =
                    $this->helper->getPriceAlert($product);
            }
        }
        //echo '<pre>';
            //print_r($options);
            //die;
        $aStockStatus['is_in_stock'] = $this->helper->isItemSalable($currentProduct);

        $this->registry->unregister('amasty_xnotif_data');
        $this->registry->register('amasty_xnotif_data', $aStockStatus);

        return $options;
    }
}