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

interface StockNoticeInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Const
     */
    const ALERT_STOCK_ID = 'alert_stock_id';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_EMAIL = 'customer_email';
    const CUSTOMER_NAME = 'customer_name';
    const PRODUCT_SKU = 'product_sku';
    const PRODUCT_ID = 'product_id';
    const WEBSITE_ID = 'website_id';
    const ADD_DATE = 'add_date';
    const SEND_DATE = 'send_date';
    const SEND_COUNT = 'send_count';
    const STATUS = 'status';
    const PARENT_ID = 'parent_id';
    const STORE_ID = 'store_id';

    const COLUMNS = [
        self::ALERT_STOCK_ID,
        self::CUSTOMER_ID,
        self::CUSTOMER_EMAIL,
        self::CUSTOMER_NAME,
        self::PRODUCT_SKU,
        self::PRODUCT_ID,
        self::WEBSITE_ID,
        self::ADD_DATE,
        self::SEND_DATE,
        self::SEND_COUNT,
        self::STATUS,
        self::PARENT_ID,
        self::STORE_ID,
    ];

    /**
     * @return int
     */
    public function getAlertStockId();

    /**
     * @param int $id
     * @return $this
     */
    public function setAlertStockId($id);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getCustomerEmail();

    /**
     * @param string $customerEmail
     * @return $this
     */
    public function setCustomerEmail($customerEmail);

    /**
     * @return string
     */
    public function getCustomerName();

    /**
     * @param string $customerName
     * @return $this
     */
    public function setCustomerName($customerName);

    /**
     * @return string
     */
    public function getProductSku();

    /**
     * @param string $sku
     * @return $this
     */
    public function setProductSku($sku);

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
    public function getWebsiteId();

    /**
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * @return string
     */
    public function getAddDate();

    /**
     * @param string $addDate
     * @return $this
     */
    public function setAddDate($addDate);

    /**
     * @return string
     */
    public function getSendDate();

    /**
     * @param string $sendDate
     * @return $this
     */
    public function setSendDate($sendDate);

    /**
     * @return string
     */
    public function getSendCount();

    /**
     * @param int $sendCount
     * @return $this
     */
    public function setSendCount($sendCount);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status);

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
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * @return \Bss\ProductStockAlertApi\Api\Data\StockNoticeExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * @param \Bss\ProductStockAlertApi\Api\Data\StockNoticeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Bss\ProductStockAlertApi\Api\Data\StockNoticeExtensionInterface $extensionAttributes
    );
}
