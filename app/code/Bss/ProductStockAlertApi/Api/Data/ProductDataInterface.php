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

interface ProductDataInterface
{
    /**
     * Const
     */
    const PRODUCT_STOCK_ALERT = 'product_stock_alert';
    const PRODUCT_STOCK_STATUS = 'product_stock_status';
    const HAS_EMAIL_SUBSCRIBED = 'has_email_subscribed';
    const PRODUCT_ID = 'product_id';
    const PARENT_ID = 'parent_id';
    const PRODUCT_TYPE = 'product_type';
    const CUSTOMER_EMAIL = 'customer_email';

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getProductStockAlert();

    /**
     * @param bool $status
     * @return $this
     */
    public function setProductStockAlert($status);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getProductStockStatus();

    /**
     * @param bool $status
     * @return $this
     */
    public function setProductStockStatus($status);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHasEmailSubscribed();

    /**
     * @param bool $hasEmail
     * @return $this
     */
    public function setHasEmailSubscribed($hasEmail);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @return int
     */
    public function getParentId();

    /**
     * @param int $parentId
     * @return $this
     */
    public function setParentId($parentId);

    /**
     * @return string
     */
    public function getProductType();

    /**
     * @param string $typeId
     * @return $this
     */
    public function setProductType($typeId);

    /**
     * @return string
     */
    public function getCustomerEmail();

    /**
     * @param string $customerEmail
     * @return $this
     */
    public function setCustomerEmail($customerEmail);
}
