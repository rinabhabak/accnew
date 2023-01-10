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
namespace Magedelight\Cybersource\Model\Payment;

class Cards
{
    const CARDS_NAMESPACE = 'magedelight_cybersource_cards';
    const CARD_ID_KEY = 'id';
    const CARD_PROCESSED_AMOUNT_KEY = 'processed_amount';
    const CARD_CAPTURED_AMOUNT_KEY = 'captured_amount';
    const CARD_REFUNDED_AMOUNT_KEY = 'refunded_amount';

    /**
     * Cards information.
     *
     * @var mixed
     */
    protected $_cards = array();

    /**
     * Payment instance.
     */
    protected $_payment = null;

    /**
     * Set payment instance for storing credit card information and partial authorizations.
     */
    public function setPayment(\Magento\Sales\Model\Order\Payment $payment)
    {
         $this->_payment = $payment;
        $paymentCardsInformation = $this->_payment->getAdditionalInformation(self::CARDS_NAMESPACE);
        if ($paymentCardsInformation) {
            $this->_cards = $paymentCardsInformation;
        }
        else
        {
           $additionalInformations = $this->_payment->getAdditionalInformation();
            $additionalCardInfo = [];
            foreach ($additionalInformations as $key => $value) {
                $additionalInfo[$key] = $value;
            }
            if (isset($additionalInfo["id"])) {
                $additionalCardInfo[$additionalInfo["id"]] = $additionalInfo;
            }
            $paymentCardsInformation = $additionalCardInfo;
            if ($paymentCardsInformation) {
                $this->_cards = $paymentCardsInformation;
            }
        }
        return $this;
    }

    /**
     * Add based on $cardInfo card to payment and return Id of new item.
     *
     * @param mixed $cardInfo
     *
     * @return string
     */
    public function registerCard($cardInfo = array())
    {
        $this->_isPaymentValid();
        $cardId = md5(microtime(1));
        $cardInfo[self::CARD_ID_KEY] = $cardId;
        $this->_cards[$cardId] = $cardInfo;
        $this->_payment->setAdditionalInformation(self::CARDS_NAMESPACE, $this->_cards);

        return $this->getCard($cardId);
    }

    /**
     * Save data from card object in cards storage.
     */
    public function updateCard($card)
    {
        $cardId = $card->getData(self::CARD_ID_KEY);
        if ($cardId && isset($this->_cards[$cardId])) {
            $this->_cards[$cardId] = $card->getData();
            $this->_payment->setAdditionalInformation(self::CARDS_NAMESPACE, $this->_cards);
        }

        return $this;
    }

    /**
     * Retrieve card by ID.
     *
     * @param string $cardId
     *
     * @return Varien_Object|bool
     */
    public function getCard($cardId)
    {
        if (isset($this->_cards[$cardId])) {
            $card = new \Magento\Framework\DataObject($this->_cards[$cardId]);

            return $card;
        }

        return false;
    }

    /**
     * Get all stored cards.
     *
     * @return array
     */
    public function getCards()
    {
        $this->_isPaymentValid();
        $_cards = array();
        foreach (array_keys($this->_cards) as $key) {
            $_cards[$key] = $this->getCard($key);
        }

        return $_cards;
    }

    /**
     * Return count of saved cards.
     *
     * @return int
     */
    public function getCardsCount()
    {
        $this->_isPaymentValid();

        return count($this->_cards);
    }

    /**
     * Return processed amount for all cards.
     *
     * @return float
     */
    public function getProcessedAmount()
    {
        return $this->_getAmount(self::CARD_PROCESSED_AMOUNT_KEY);
    }

    /**
     * Return captured amount for all cards.
     *
     * @return float
     */
    public function getCapturedAmount()
    {
        return $this->_getAmount(self::CARD_CAPTURED_AMOUNT_KEY);
    }

    /**
     * Return refunded amount for all cards.
     *
     * @return float
     */
    public function getRefundedAmount()
    {
        return $this->_getAmount(self::CARD_REFUNDED_AMOUNT_KEY);
    }

    /**
     * Remove all cards from payment instance.
     */
    public function flushCards()
    {
        $this->_cards = array();
        $this->_payment->setAdditionalInformation(self::CARDS_NAMESPACE, null);

        return $this;
    }

    /**
     * Check for payment instace present.
     *
     * @throws Exception
     */
    protected function _isPaymentValid()
    {
        if (!$this->_payment) {
            throw new \Exception('Payment instance is not set');
        }
    }
    /**
     * Return total for cards data fields.
     *
     * $param string $key
     *
     * @return float
     */
    public function _getAmount($key)
    {
        $amount = 0;
        foreach ($this->_cards as $card) {
            if (isset($card[$key])) {
                $amount += $card[$key];
            }
        }

        return $amount;
    }
}
