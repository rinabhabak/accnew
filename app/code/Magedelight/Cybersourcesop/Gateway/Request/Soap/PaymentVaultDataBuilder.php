<?php
/**
* Magedelight
* Copyright (C) 2017 Magedelight <info@magedelight.com>
*
* NOTICE OF LICENSE
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
*
* @category Magedelight
* @package Magedelight_Cybersourcedc
* @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
*/
namespace Magedelight\Cybersourcesop\Gateway\Request\Soap;

use Magento\Payment\Gateway\ConfigInterface;
use Magedelight\Cybersourcesop\Observer\DataAssignObserver;
use Magedelight\Cybersourcesop\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magedelight\Cybersourcesop\Gateway\Request\SilentOrder\TransactionDataBuilder;
use Magento\Framework\Math\Random;

/**
 * Payment Data Builder
 */
class PaymentVaultDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * The billing amount of the request. This value must be greater than 0,
     * and must match the currency format of the merchant account.
     */
    const AMOUNT = 'amount';
    const PURCHASETOTALS = 'purchaseTotals';
    const CURRENCY = 'currency';
    const RECURRINGSUBS = 'recurringSubscriptionInfo';
    const CARD = 'card';
    const SUBSCRIPTIONID = 'subscriptionID';
    const CV_NUMBER = 'cvNumber';
    const GRANDTOTALAMOUNT = 'grandTotalAmount';
    const MERCHANTREFERENCECODE = 'merchantReferenceCode';

    /**
     * Order ID
     */
    const ORDER_ID = 'orderId';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(ConfigInterface $config, SubjectReader $subjectReader, PaymentTokenManagementInterface $tokenManagement,Random $random)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->tokenManagement = $tokenManagement;
        $this->random = $random;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();
        $publicHash = $payment->getAdditionalInformation(
                            DataAssignObserver::PUBLIC_HASH);
        $customerId = $order->getCustomerId();
        $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);
         if (!$paymentToken) {
            throw new Exception('No available payment tokens');
        }

        $result = [
            self::RECURRINGSUBS => [
                 self::SUBSCRIPTIONID => $paymentToken->getGatewayToken()
            ],
            self::PURCHASETOTALS => [
                 self::GRANDTOTALAMOUNT => $this->formatPrice($this->subjectReader->readAmount($buildSubject)),
                 self::CURRENCY => $payment->getOrder()->getBaseCurrencyCode(),
            ],
            self::ORDER_ID => $order->getOrderIncrementId(),
           
        ];
        $payment->setAdditionalInformation(
                    TransactionDataBuilder::REFERENCE_NUMBER,$this->getReferenceNumber()
                );
        return $result;
    }
     /**
     * Returns reference number
     *
     * @param InfoInterface $payment
     * @return string
     */
    private function getReferenceNumber()
    {
        return $this->random->getRandomString(TransactionDataBuilder::RANDOM_LENGTH, Random::CHARS_DIGITS);
    }
}
