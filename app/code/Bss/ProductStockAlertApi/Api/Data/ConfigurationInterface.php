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

interface ConfigurationInterface
{
    /**
     * Const group general
     */
    const ALLOW_STOCK = 'allow_stock';
    const ALLOW_CUSTOMER = 'allow_customer';
    const EMAIL_BASED_QTY = 'email_based_qty';
    const MESSAGE = 'message';
    const STOP_MESSAGE = 'stop_message';
    const SEND_LIMIT = 'send_limit';
    const ALLOW_STOCK_QTY = 'allow_stock_qty';

    /**
     * Const group design
     */
    const BUTTON_TEXT = 'button_text';
    const STOP_BUTTON_TEXT = 'stop_button_text';
    const BUTTON_TEXT_COLOR = 'button_text_color';
    const BUTTON_COLOR = 'button_color';

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAllowStock();

    /**
     * @param bool $status
     * @return $this
     */
    public function setAllowStock($status);

    /**
     * @return array
     */
    public function getAllowCustomer();

    /**
     * @param array $allowCustomer
     * @return $this
     */
    public function setAllowCustomer($allowCustomer);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getEmailBasedQty();

    /**
     * @param bool $emailBasedQty
     * @return $this
     */
    public function setEmailBasedQty($emailBasedQty);

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * @return string
     */
    public function getStopMessage();

    /**
     * @param string $stopMessage
     * @return $this
     */
    public function setStopMessage($stopMessage);

    /**
     * @return int
     */
    public function getSendLimit();

    /**
     * @param int $limitEmail
     * @return $this
     */
    public function setSendLimit($limitEmail);

    /**
     * @return int
     */
    public function getAllowStockQty();

    /**
     * @param int $allowStockQty
     * @return $this
     */
    public function setAllowStockQty($allowStockQty);

    /**
     * @return string
     */
    public function getButtonText();

    /**
     * @param string $buttonText
     * @return $this
     */
    public function setButtonText($buttonText);

    /**
     * @return string
     */
    public function getStopButtonText();

    /**
     * @param string $stopButtonText
     * @return $this
     */
    public function setStopButtonText($stopButtonText);

    /**
     * @return string
     */
    public function getButtonTextColor();

    /**
     * @param string $buttonTextColor
     * @return $this
     */
    public function setButtonTextColor($buttonTextColor);

    /**
     * @return string
     */
    public function getButtonColor();

    /**
     * @param string $buttonColor
     * @return $this
     */
    public function setButtonColor($buttonColor);
}
