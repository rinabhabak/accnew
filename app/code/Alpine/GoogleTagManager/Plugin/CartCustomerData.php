<?php
/**
 * Alpine_GoogleTagManager
 *
 * @category    Alpine
 * @package     Alpine_GoogleTagManager
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc
 * @author      Evgeniy Derevyanko <evgeniy.derevyanko@alpineinc.com>
 */

namespace Alpine\GoogleTagManager\Plugin;

use \Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use \Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class CartCustomerData
 */
class CartCustomerData
{
    /**
     * products type
     *
     * @var string
     */
    const PRODUCT_TYPE = 'configurable';

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * CartCustomerData constructor.
     *
     * @param Configurable $configurable
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Configurable $configurable,
        ProductRepositoryInterface $productRepository
    ) {
        $this->configurable = $configurable;
        $this->productRepository = $productRepository;
    }

    /**
     * Plugin method
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        foreach ($result['items'] as &$product) {
            if ($product['product_type'] == self::PRODUCT_TYPE) {
                $product['product_parent_sku'] = $this->productRepository->getById($product['product_id'])->getSku();
            }
        }

        return $result;
    }
}
