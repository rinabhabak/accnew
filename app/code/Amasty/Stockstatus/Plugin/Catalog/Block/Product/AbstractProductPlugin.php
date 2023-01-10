<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Plugin\Catalog\Block\Product;

class AbstractProductPlugin
{
    /**
     * @var \Amasty\Stockstatus\Helper\Data
     */
    private $helper;

    /**
     * @var array
     */
    protected $matchedNames = [
            'product.info.configurable',
            'product.info.simple',
            'product.info.bundle',
            'product.info.virtual',
            'product.info.downloadable',
            'product.info.grouped.stock'
        ];

    public function __construct(
        \Amasty\Stockstatus\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Block\Product\AbstractProduct $subject
     * @param $result
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(
        \Magento\Catalog\Block\Product\AbstractProduct $subject,
        $result
    ) {
        $name = $subject->getNameInLayout();

        if (in_array($name, $this->matchedNames)
            || strpos($name, 'product.info.type_schedule_block') !== false
        ) {
            $status = $this->helper->showStockStatus($subject->getProduct(), 1, 0);
            if ($status != '') {
                $result = $status . $this->helper->getInfoBlock();
            }
        }

        return  $result;
    }
}
