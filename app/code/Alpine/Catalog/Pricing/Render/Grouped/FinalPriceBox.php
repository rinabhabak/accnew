<?php
/**
 * Alpine_Catalog
 *
 * @category     Alpine
 * @package      Alpine_Catalog
 * @copyright    Copyright (C) 2018 Alpine Consulting, Inc
 * @author       Iurii Ziukov <iurii.ziukov@alpineinc.com>
 */

namespace Alpine\Catalog\Pricing\Render\Grouped;

use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Render\FinalPriceBox as MagentoFinalPriceBox;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;

/**
 * Alpine\Catalog\Pricing\Render\Grouped\FinalPriceBox
 *
 * @category Alpine
 * @package  Alpine_Catalog
 */
class FinalPriceBox extends MagentoFinalPriceBox
{
    /**
     * CalculatorInterface
     *
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * FinalPriceBox constructor.
     *
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param CalculatorInterface $calculator
     * @param array $data
     * @param SalableResolverInterface|null $salableResolver
     * @param MinimalPriceCalculatorInterface|null $minimalPriceCalculator
     */
    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        CalculatorInterface $calculator,
        array $data = [],
        SalableResolverInterface $salableResolver = null,
        MinimalPriceCalculatorInterface $minimalPriceCalculator = null
    ) {
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data,
            $salableResolver,
            $minimalPriceCalculator
        );

        $this->calculator = $calculator;
    }

    /**
     * Should we display a range price
     *
     * @return bool
     */
    public function showRangePrice()
    {
        $product = $this->getSaleableItem();
        $minPrice = $product->getMinPrice();
        $maxPrice = $product->getMaxPrice();

        return $minPrice && $maxPrice && $minPrice != $maxPrice;
    }

    /**
     * Should we display price
     *
     * @return bool
     */
    public function showPrice()
    {
        return $this->getSaleableItem()->getMinPrice() > 0;
    }

    /**
     * Get mininal amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMinAmount()
    {
        $product = $this->getSaleableItem();

        return $this->calculator->getAmount($product->getMinPrice(), $product);
    }

    /**
     * Get maximum amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaxAmount()
    {
        $product = $this->getSaleableItem();

        return $this->calculator->getAmount($product->getMaxPrice(), $product);
    }
}
