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

use Bss\ProductStockAlertApi\Api\Data\ProductDataInterface;
use Magento\Framework\DataObject;

class ProductData extends DataObject implements ProductDataInterface
{
    /**
     * @inheritDoc
     */
    public function getProductStockAlert()
    {
        return $this->getData(self::PRODUCT_STOCK_ALERT);
    }

    /**
     * @inheritDoc
     */
    public function setProductStockAlert($status)
    {
        return $this->setData(self::PRODUCT_STOCK_ALERT, $status);
    }

    /**
     * @inheritDoc
     */
    public function getProductStockStatus()
    {
        return $this->getData(self::PRODUCT_STOCK_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setProductStockStatus($status)
    {
        return $this->setData(self::PRODUCT_STOCK_STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getHasEmailSubscribed()
    {
        return $this->getData(self::HAS_EMAIL_SUBSCRIBED);
    }

    /**
     * @inheritDoc
     */
    public function setHasEmailSubscribed($hasEmail)
    {
        return $this->setData(self::HAS_EMAIL_SUBSCRIBED, $hasEmail);
    }

    /**
     * @inheritDoc
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritDoc
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setParentId($parentId)
    {
        return $this->setData(self::PARENT_ID, $parentId);
    }

    /**
     * @inheritDoc
     */
    public function getProductType()
    {
        return $this->getData(self::PRODUCT_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setProductType($typeId)
    {
        return $this->setData(self::PRODUCT_TYPE, $typeId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }
}
