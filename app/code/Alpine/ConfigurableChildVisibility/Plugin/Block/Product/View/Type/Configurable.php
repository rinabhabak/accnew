<?php
/* Alpine_ConfigurableChildVisibility
*
* @category    Alpine
* @package     Alpine_Accuride
* @copyright   Copyright (c) 2018 Alpine Consulting, Inc
* @author      Derevyanko Evgeniy <evgeniy.derevyanko@alpineinc.com>
*/
declare(strict_types=1);

namespace Alpine\ConfigurableChildVisibility\Plugin\Block\Product\View\Type;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as Subject;

/**
 * Select Configurable Simple Products class
 */
class Configurable
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;
    
    /**
     * @var \Psr\Log\LoggerInterface 
     */
    protected $logger;
    
    /**
     * Configurable constructor
     *
     * @param StockConfigurationInterface $stockConfiguration
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->logger = $logger;
    }

    /**
     * Get All used products for configurable
     *
     * @param Subject $subject
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeGetAllowProducts(
        Subject $subject
    ) {
        if (!$subject->hasAllowProducts() &&
            $this->stockConfiguration->isShowOutOfStock()) {
            /** @var Product $product */
            $product = $subject->getProduct();
            $allowProducts = [];
            $usedProducts = $product->getTypeInstance(true)
                ->getUsedProducts($product);
            /** @var Product $usedProduct */
            foreach ($usedProducts as $usedProduct) {
                if ($usedProduct->getStatus() == Status::STATUS_ENABLED) {
                    $allowProducts[] = $usedProduct;
                }
            }
            $this->logger->debug(json_encode($allowProducts));
            $subject->setAllowProducts($allowProducts);
        }
        
        return $subject->getData('allow_products');
    }
}
