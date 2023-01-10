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
namespace Bss\ProductStockAlertApi\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface StockNoticeManagementInterface
{
    /**
     * @param int $storeId
     * @return \Bss\ProductStockAlertApi\Api\Data\ConfigurationInterface
     * @throws NoSuchEntityException
     */
    public function getConfiguration($storeId);

    /**
     * @param int $productId
     * @param int $websiteId
     * @param int $customerId
     * @return \Bss\ProductStockAlertApi\Api\Data\ProductDataResultInterface
     * @throws LocalizedException
     */
    public function getProductData($productId, $websiteId, $customerId);

    /**
     * @param int $productId
     * @param int $parentId
     * @param int $websiteId
     * @param string $email
     * @param int $customerId
     * @return \Bss\ProductStockAlertApi\Api\Data\ResultDataInterface
     */
    public function subscribeStockNotice($productId, $parentId, $websiteId, $email, $customerId);

    /**
     * @param int $productId
     * @param int $parentId
     * @param int $websiteId
     * @param int $customerId
     * @return \Bss\ProductStockAlertApi\Api\Data\ResultDataInterface
     */
    public function unsubscribeStockNotice($productId, $parentId, $websiteId, $customerId);

    /**
     * @param int $websiteId
     * @param int $customerId
     * @return \Bss\ProductStockAlertApi\Api\Data\ResultDataInterface
     */
    public function unsubscribeAllStockNotice($websiteId, $customerId);

    /**
     * @param int $customerId
     * @return \Bss\ProductStockAlertApi\Api\Data\StockNoticeResultInterface
     */
    public function getListByCustomer($customerId);

    /**
     * @param int $stockId
     * @param int $customerId
     * @return \Bss\ProductStockAlertApi\Api\Data\StockNoticeResultInterface
     */
    public function getById($stockId, $customerId);
}
