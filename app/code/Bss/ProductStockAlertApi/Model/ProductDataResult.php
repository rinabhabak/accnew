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
namespace Bss\ProductStockAlertApi\Model;

use Magento\Framework\DataObject;
use Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterface;

class ProductDataResult extends DataObject implements ProductDataResultInterface
{
    /**
     * @inheritDoc
     */
    public function getItems()
    {
        return $this->getData('items');
    }

    /**
     * @inheritDoc
     */
    public function setItems($items)
    {
        return $this->setData('items', $items);
    }
}
