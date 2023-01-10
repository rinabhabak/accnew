<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Cybersource
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Cybersource\Block;

class Info extends \Magento\Payment\Block\Info\Cc
{
    /**
     * Payment config model.
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_isCheckoutProgressBlockFlag = true;
    protected $cybersourceHelper;
    protected $_paymentConfig;
    protected $paymentModel;
    protected $_cybersourceConfig;
    protected $_template = 'Magedelight_Cybersource::info.phtml';
    protected $cardpayment;
    protected $storeManager;
    protected $currencyHelper;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config                    $paymentConfig
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magedelight\Cybersource\Model\Config $cybersourceConfig,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magedelight\Cybersource\Model\Payment\Cards $cardpayment,
        \Magento\Sales\Model\Order\Payment\Transaction $payment,
        \Magedelight\Cybersource\Helper\Data $cybersourceHelper,
         \Magento\Framework\Pricing\Helper\Data $currencyHelper,
        array $data = []
    ) {
       
        $this->storeManager = $storeManager;
        $this->_paymentConfig = $paymentConfig;
        $this->_cybersourceConfig = $cybersourceConfig;
        $this->paymentModel = $payment;
        $this->cardpayment = $cardpayment;
        $this->currencyHelper = $currencyHelper;
        $this->cybersourceHelper = $cybersourceHelper;
         parent::__construct($context,$paymentConfig, $data);
    }

    public function setCheckoutProgressBlock($flag)
    {
        $this->_isCheckoutProgressBlockFlag = $flag;

        return $this;
    }
    public function getSpecificInformation()
    {
        return $this->_prepareSpecificInformation()->getData();
    }
    public function getCards()
    {
        $this->cardpayment->setPayment($this->getInfo());
        $cardsData = $this->cardpayment->getCards();
        $cards = array();
            if (is_array($cardsData)) {
                foreach ($cardsData as $cardInfo) {
                    $data = array();
                    $lastTransactionId = $this->getData('info')->getData('cc_trans_id');
                     $cardTransactionId = $cardInfo->getTransactionId();
                    if ($lastTransactionId == $cardTransactionId) {
                        if ($cardInfo->getProcessedAmount()) {
                            $amount = $this->currencyHelper->currency($cardInfo->getProcessedAmount(), true, false);
                            $data['Processed Amount'] = $amount;
                        }

                        if ($cardInfo->getBalanceOnCard() && is_numeric($cardInfo->getBalanceOnCard())) {
                            $balance = $this->currencyHelper->currency($cardInfo->getBalanceOnCard(), true, false);
                            $data['Remaining Balance'] = $balance;
                        }
                        if ($this->cybersourceHelper->checkAdmin()) {
                            if ($cardInfo->getApprovalCode() && is_string($cardInfo->getApprovalCode())) {
                                $data['Approval Code'] = $cardInfo->getApprovalCode();
                            }

                            if ($cardInfo->getMethod() && is_numeric($cardInfo->getMethod())) {
                                $data['Method'] = ($cardInfo->getMethod() == 'CC') ? __('Credit Card') : __('eCheck');
                            }

                            if ($cardInfo->getLastTransId() && $cardInfo->getLastTransId()) {
                                $data['Transaction Id'] = str_replace(array('-capture', '-void', '-refund'), '', $cardInfo->getLastTransId());
                            }

                            if ($cardInfo->getAvsResultCode() && is_string($cardInfo->getAvsResultCode())) {
                                $data['AVS Response'] = $this->cybersourceHelper->getAvsLabel($cardInfo->getAvsResultCode());
                            }

                            if ($cardInfo->getCVNResultCode() && is_string($cardInfo->getCVNResultCode())) {
                                $data['CVN Response'] = $this->cybersourceHelper->getCvnLabel($cardInfo->getCVNResultCode());
                            }

                            if ($cardInfo->getCardCodeResponseCode() && is_string($cardInfo->getreconciliationID())) {
                                $data['CCV Response'] = $cardInfo->getCardCodeResponseCode();
                            }

                            if ($cardInfo->getMerchantReferenceCode() && is_string($cardInfo->getMerchantReferenceCode())) {
                                $data['Merchant Reference Code'] = $cardInfo->getMerchantReferenceCode();
                            }
                        }

                        $this->setCardInfoObject($cardInfo);

                        $cards[] = array_merge($this->getSpecificInformation(), $data);
                        $this->unsCardInfoObject();
                        $this->_paymentSpecificInformation = null;
                    }
                }
            }
        if ($this->getInfo()->getCcType() && $this->_isCheckoutProgressBlockFlag && count($cards) == 0) {
            $cards[] = $this->getSpecificInformation();
        }
        return $cards;
    }
}
