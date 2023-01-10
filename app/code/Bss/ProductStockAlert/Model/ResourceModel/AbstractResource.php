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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model\ResourceModel;

/**
 * Class AbstractResource
 * @package Bss\ProductStockAlert\Model\ResourceModel
 */
abstract class AbstractResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Retrieve alert row by object parameters
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getAlertRow(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();
        if ($object->getCustomerId() && $object->getProductId() && $object->getWebsiteId()) {
            $select = $connection->select()->from(
                $this->getMainTable()
            )->where(
                'customer_id = :customer_id'
            )->where(
                'product_id  = :product_id'
            )->where(
                'website_id  = :website_id'
            );
            $bind = [
                ':customer_id' => $object->getCustomerId(),
                ':product_id' => $object->getProductId(),
                ':website_id' => $object->getWebsiteId(),
            ];
            return $connection->fetchRow($select, $bind);
        }
        return false;
    }

    /**
     * Retrieve alert row by object parameters
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getAlertRowGuest(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();
        if ($object->getCustomerEmail() && $object->getProductId() && $object->getWebsiteId()) {
            $select = $connection->select()->from(
                $this->getMainTable()
            )->where(
                'customer_email = :customer_email'
            )->where(
                'product_id  = :product_id'
            )->where(
                'website_id  = :website_id'
            );
            $bind = [
                ':customer_email' => $object->getCustomerEmail(),
                ':product_id' => $object->getProductId(),
                ':website_id' => $object->getWebsiteId(),
            ];
            return $connection->fetchRow($select, $bind);
        }
        return false;
    }

    /**
     * Load object data by parameters
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByParam(\Magento\Framework\Model\AbstractModel $object)
    {
        $row = $this->_getAlertRow($object);
        if ($row) {
            $object->setData($row);
        }
        return $this;
    }

    /**
     * Load object data by parameters
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByParamGuest(\Magento\Framework\Model\AbstractModel $object)
    {
        $row = $this->_getAlertRowGuest($object);
        if ($row) {
            $object->setData($row);
        }
        return $this;
    }

    /**
     * Delete all customer alerts on website
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param int $customerId
     * @param int $websiteId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteCustomer(
        $customerId,
        $websiteId = null
    ) {
        $connection = $this->getConnection();
        $where = [];
        $where[] = $connection->quoteInto('customer_id=?', $customerId);
        if ($websiteId) {
            $where[] = $connection->quoteInto('website_id=?', $websiteId);
        }
        $connection->delete($this->getMainTable(), $where);
        return $this;
    }
}
