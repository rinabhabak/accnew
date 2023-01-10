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

use Bss\ProductStockAlertApi\Api\Data\ConfigurationInterface;
use Magento\Framework\DataObject;

class Configuration extends DataObject implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getAllowStock()
    {
        return $this->getData(self::ALLOW_STOCK);
    }

    /**
     * @inheritDoc
     */
    public function setAllowStock($status)
    {
        return $this->setData(self::ALLOW_STOCK, $status);
    }

    /**
     * @inheritDoc
     */
    public function getAllowCustomer()
    {
        return $this->getData(self::ALLOW_CUSTOMER);
    }

    /**
     * @inheritDoc
     */
    public function setAllowCustomer($allowCustomer)
    {
        return $this->setData(self::ALLOW_CUSTOMER, $allowCustomer);
    }

    /**
     * @inheritDoc
     */
    public function getEmailBasedQty()
    {
        return $this->getData(self::EMAIL_BASED_QTY);
    }

    /**
     * @inheritDoc
     */
    public function setEmailBasedQty($emailBasedQty)
    {
        return $this->setData(self::EMAIL_BASED_QTY, $emailBasedQty);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @inheritDoc
     */
    public function getStopMessage()
    {
        return $this->getData(self::STOP_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setStopMessage($stopMessage)
    {
        return $this->setData(self::STOP_MESSAGE, $stopMessage);
    }

    /**
     * @inheritDoc
     */
    public function getSendLimit()
    {
        return $this->getData(self::SEND_LIMIT);
    }

    /**
     * @inheritDoc
     */
    public function setSendLimit($limitEmail)
    {
        return $this->setData(self::SEND_LIMIT, $limitEmail);
    }

    /**
     * @inheritDoc
     */
    public function getAllowStockQty()
    {
        return $this->getData(self::ALLOW_STOCK_QTY);
    }

    /**
     * @inheritDoc
     */
    public function setAllowStockQty($allowStockQty)
    {
        return $this->setData(self::ALLOW_STOCK_QTY, $allowStockQty);
    }

    /**
     * @inheritDoc
     */
    public function getButtonText()
    {
        return $this->getData(self::BUTTON_TEXT);
    }

    /**
     * @inheritDoc
     */
    public function setButtonText($buttonText)
    {
        return $this->setData(self::BUTTON_TEXT, $buttonText);
    }

    /**
     * @inheritDoc
     */
    public function getStopButtonText()
    {
        return $this->getData(self::STOP_BUTTON_TEXT);
    }

    /**
     * @inheritDoc
     */
    public function setStopButtonText($stopButtonText)
    {
        return $this->setData(self::STOP_BUTTON_TEXT, $stopButtonText);
    }

    /**
     * @inheritDoc
     */
    public function getButtonTextColor()
    {
        return $this->getData(self::BUTTON_TEXT_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setButtonTextColor($buttonTextColor)
    {
        return $this->setData(self::BUTTON_TEXT_COLOR, $buttonTextColor);
    }

    /**
     * @inheritDoc
     */
    public function getButtonColor()
    {
        return $this->getData(self::BUTTON_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setButtonColor($buttonColor)
    {
        return $this->setData(self::BUTTON_COLOR, $buttonColor);
    }
}
