<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Cybersource
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Cybersource\Block;

class Form extends \Magento\Payment\Block\Form\Cc
{
    protected $_paymentConfig;
    protected $_cybersourcePaymentConfig;
    protected $checkoutsession;
    protected $_template = 'Magedelight_Cybersource::form.phtml';
    protected $confiprovider;
    protected $config;
    protected $items = [];
    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
            \Magedelight\Cybersource\Model\ConfigProvider $configprovider,
            \Magedelight\Cybersource\Model\Config $config,
            \Magento\Checkout\Model\Session $checkoutsession,
            \Magento\Payment\Model\Config $paymentConfig, \Magedelight\Cybersource\Model\Config $cybersourceConfig, array $data = [])
    {
        parent::__construct($context, $paymentConfig, $data);
        $this->_paymentConfig = $paymentConfig;
        $this->confiprovider = $configprovider;
        $this->config = $config;
        $this->checkoutsession = $checkoutsession;
        $this->_cybersourcePaymentConfig = $cybersourceConfig;
    }

    public function getCcAvailableTypes()
    {
        $types = $this->_paymentConfig->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $this->_cybersourcePaymentConfig->getCcTypes();
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code => $name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }

        return $types;
    }
    public function getQuoteItems()
    {
        $quote = $this->checkoutsession->getQuote();
        if ($quote && $quote->getId()) {
            $this->items = $quote->getAllItems();
        }

        return $this->items;
    }
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if ($months === null) {
            $months[0] = __('Month');
            $months = array_merge($months, $this->_paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }

        return $months;
    }
    public function getSaveCardOptional()
    {
        return $this->config->getSaveCardOptional();
    }
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if ($years === null) {
            $years = $this->_paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    public function hasVerification()
    {
        if ($this->getMethod()) {
            $configData = $this->_cybersourcePaymentConfig->isCardVerificationEnabled();
            if ($configData === null) {
                return true;
            }

            return $configData;
        }

        return true;
    }

    public function hasSsCardType()
    {
        $availableTypes = explode(',', $this->_cybersourcePaymentConfig->getCcTypes());
        $ssPresenations = array_intersect(['SS', 'SM', 'SO'], $availableTypes);
        if ($availableTypes && count($ssPresenations) > 0) {
            return true;
        }

        return false;
    }

    public function getSsStartYears()
    {
        $years = [];
        $first = date('Y');

        for ($index = 5; $index >= 0; --$index) {
            $year = $first - $index;
            $years[$year] = $year;
        }
        $years = [0 => __('Year')] + $years;

        return $years;
    }
    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch('payment_form_block_to_html_before', ['block' => $this]);

        return parent::_toHtml();
    }
    public function getCustomerSavedCards()
    {
        return $this->confiprovider->getStoredCards();
    }
}
