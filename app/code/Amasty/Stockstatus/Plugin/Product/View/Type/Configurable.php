<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Plugin\Product\View\Type;

use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Amasty\Stockstatus\Model\Source\Outofstock;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as NativeConfigurable;

class Configurable
{
    /**
     * @var bool
     */
    private $isProductPage;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $catalogProduct;
    
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;
    
    /**
     * @var \Amasty\Stockstatus\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Data
     */
    private $configurableHelper;

    /**
     * @var array
     */
    private $originalAllowedProducts = [];

    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    public function __construct(
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Amasty\Stockstatus\Helper\Data $helper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ConfigurableProduct\Helper\Data $configurableHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->catalogProduct = $catalogProduct;
        $this->stockRegistry = $stockRegistry;
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->storeManager = $storeManager;
        $this->configurableHelper = $configurableHelper;
        $this->jsonDecoder = $jsonDecoder;
        $this->isProductPage = $request->getFullActionName() == 'catalog_product_view';
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @return array
     */
    public function beforeGetAllowProducts(
        $subject
    ) {
        if ($this->isProductPage()
            && !$subject->hasAllowProducts()
            && $this->helper->getOutofstockVisibility() != Outofstock::MAGENTO_LOGIC
        ) {
            $products = [];
            $websiteId =  $this->storeManager->getWebsite()->getId();
            $allProducts = $subject->getProduct()->getTypeInstance()->getUsedProducts($subject->getProduct(), null);
            foreach ($allProducts as $product) {
                /* remove code for showing out of stock options*/
                if ($product->getStatus() == Status::STATUS_ENABLED) {
                    $products[] = $product;
                }
                $stockStatus = $this->stockRegistry->getStockStatus(
                    $product->getId(),
                    $websiteId
                );
                if ($stockStatus->getStockStatus()) {
                    $this->originalAllowedProducts[] = $product;
                }
            }
            $subject->setAllowProducts($products);
        }

        return [];
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterToHtml(
        $subject,
        $html
    ) {
        if (!$this->isProductPage()
            || strpos($html, 'amstockstatusRenderer.init') !== false
            || !($subject->getNameInLayout() == 'product.info.options.configurable'
                || ($subject->getNameInLayout() == 'product.info.options.swatches' && $this->isChangeStatus())
            )
        ) {
            return $html;
        }

        $instance = $subject->getProduct()->getTypeInstance(true);
        $allProducts = $instance->getUsedProducts($subject->getProduct());
        $attributes = $instance->getConfigurableAttributes($subject->getProduct());
        $shouldLoadStock = $this->shouldLoadStock();
        $statusIconOnly = (int)$this->helper->getModuleConfig('general/icon_only');

        $childData = [];
        foreach ($allProducts as $product) {
            $key = $this->getKey($attributes, $product);

            if ($key) {
                $childData[$key] = [
                    'custom_status_text'     => $this->helper->getCustomStockStatusText($product),
                    'custom_status'          => $this->helper->showStockStatus($product),
                    'custom_status_icon'     => $this->helper->getStatusIconImage($product),
                    'custom_status_icon_only'=> $statusIconOnly,
                    'product_id'             => $product->getId()
                ];

                if ($shouldLoadStock) {
                    $stockStatus = $this->stockRegistry->getStockStatusBySku(
                        $product->getSku(),
                        $this->storeManager->getWebsite()->getId()
                    );
                    $saleable = $stockStatus->getStockStatus() && $this->verifyStock($stockStatus);

                    $childData[$key]['is_in_stock'] = (int)$saleable;
                    if (!$saleable) {
                        $product->setData('is_salable', 0);
                        $childData[$key]['stockalert'] =
                            $this->helper->getStockAlert($product);
                    }

                    if (!$childData[$key]['is_in_stock'] && !$childData[$key]['custom_status']) {
                        $childData[$key]['custom_status'] = __('Out of Stock');
                        $childData[$key]['custom_status_text'] = __('Out of Stock');
                    }
                }

                $childData[$key]['pricealert'] = $this->helper->getPriceAlert($product);

                /* add status for previous option when all statuses are the same*/
                $pos = strrpos($key, ",");
                if ($pos) {
                    $newKey = substr($key, 0, $pos);
                    if (array_key_exists($newKey, $childData)) {
                        if ($childData[$newKey]['custom_status'] !=  $childData[$key]['custom_status']) {
                            $childData[$newKey] = null;
                        }
                    } else {
                        $childData[$newKey] =  $childData[$key];
                    }
                }
            }
        }

        $childData['changeConfigurableStatus'] = $this->isChangeStatus();
        $childData['type'] = $subject->getNameInLayout();
        $childData['info_block'] = $this->helper->getInfoBlock();

        $html  .=
            '<script>
                require(["jquery", "jquery/ui", "Amasty_Stockstatus/js/amstockstatus"],
                function ($, ui, amstockstatusRenderer) {
                    amstockstatusRenderer.init(' . $this->jsonEncoder->encode($childData) . ');
                });
            </script>';

        return $html;
    }

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute[] $attributes
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array|string
     */
    protected function getKey($attributes, \Magento\Catalog\Model\Product $product)
    {
        $key = [];
        foreach ($attributes as $attribute) {
            $key[] = $product->getData(
                $attribute->getData('product_attribute')->getData(
                    'attribute_code'
                )
            );
        }

        $key =  implode(',', $key);

        return $key;
    }

    /**
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus
     * @return bool
     */
    public function verifyStock($stockStatus)
    {
        $result = true;

        $stockItem = $stockStatus->getStockItem();
        if ($stockStatus->getQty() === null && $stockItem->getManageStock()) {
            $result = false;
        }

        if ($stockItem->getBackorders() == StockItemInterface::BACKORDERS_NO
            && $stockStatus->getQty() <= $stockStatus->getMinQty()
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param NativeConfigurable$subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetJsonConfig($subject, $result)
    {
        $result = $this->jsonDecoder->decode($result);

        if ($this->helper->getOutofstockVisibility() === Outofstock::SHOW_AND_CROSSED) {
            $result['original_products'] = $this->configurableHelper->getOptions(
                $subject->getProduct(),
                $this->originalAllowedProducts
            );
        }

        return $this->jsonEncoder->encode($result);
    }

    /**
     * @return bool
     */
    protected function isProductPage()
    {
        return $this->isProductPage;
    }

    /**
     * @return int
     */
    protected function isChangeStatus()
    {
        return (int)$this->helper->getModuleConfig("configurable_products/change_custom_configurable_status");
    }

    /**
     * @return bool
     */
    protected function shouldLoadStock()
    {
        return $this->helper->getOutofstockVisibility() !== Outofstock::MAGENTO_LOGIC;
    }
}
