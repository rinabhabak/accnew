<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlertApi
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlertApi\Api\Data;

interface StockNoticeResultInterface
{
    /**
     * @return \Bss\ProductStockAlertApi\Api\Data\StockNoticeInterface[]
     */
    public function getItems();

    /**
     * @param \Bss\ProductStockAlertApi\Api\Data\StockNoticeInterface[] $items
     * @return $this
     */
    public function setItems($items);
}
