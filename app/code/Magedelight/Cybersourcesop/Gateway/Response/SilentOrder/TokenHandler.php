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
namespace Magedelight\Cybersourcesop\Gateway\Response\SilentOrder;

use Magedelight\Cybersourcesop\Gateway\Request\SilentOrder\PaymentTokenBuilder;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magedelight\Cybersourcesop\Observer\DataAssignObserver;
use Magento\Framework\Intl\DateTimeFactory;

class TokenHandler implements HandlerInterface
{
     /**
     * Map for CC type field. Magento scope => Cybersource scope
     *
     * @var array
     */
   
    static private $ccTypeMap = [
        '003' => 'AE',
        '001' => 'VI',
        '002' => 'MC',
        '004' => 'DI',
        '005' => 'DN',
        '007' => 'JCB',
        '024' => 'MD',
        '042' => 'MI'
    ];
    
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     *
     * @var Magento\Vault\Model\PaymentTokenFactory
     */
    protected $paymentCardSaveTokenFactory;
    /**
     * Constructor
     *
     * @param PaymentTokenInterfaceFactory $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param SubjectReader $subjectReader
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        SubjectReader $subjectReader,
        DateTimeFactory $dateTimeFactory,
        \Magento\Vault\Model\PaymentTokenFactory $paymentCardSaveTokenFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
       $this->subjectReader = $subjectReader;
       $this->dateTimeFactory = $dateTimeFactory;
       $this->paymentCardSaveTokenFactory = $paymentCardSaveTokenFactory;
       $this->encryptor = $encryptor;
    }
    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws \InvalidArgumentException
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($response[PaymentTokenBuilder::PAYMENT_TOKEN])) {
            return;
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        $paymentDO->getPayment()
            ->setAdditionalInformation(
                PaymentTokenBuilder::PAYMENT_TOKEN,
                $response[PaymentTokenBuilder::PAYMENT_TOKEN]
            );

        if (isset($response[TransactionIdHandler::TRANSACTION_ID])) {
            $paymentDO->getPayment()
                ->setAdditionalInformation(
                    TransactionIdHandler::TRANSACTION_ID,
                    $response[TransactionIdHandler::TRANSACTION_ID]
                );
        }

        $payment = $paymentDO->getPayment();
        $customerId = $payment->getQuote()->getCustomerId();
        $active_token = $payment->getAdditionalInformation(DataAssignObserver::IS_ACTIVE_PAYMENT_TOKEN_ENABLER);
        if($active_token && $customerId!=NULL)
        {
            $vaultCard = [];
            $vaultCard['gateway_token'] = $response[PaymentTokenBuilder::PAYMENT_TOKEN];
            $vaultCard['customer_id'] = $customerId;
            $vaultCard['is_active'] = true;
            $vaultCard['is_visible'] = true;
            $vaultCard['payment_method_code'] = $payment->getMethod();
            $vaultCard['type'] = 'card';
            $expires_at = $this->getExpirationDate($response);
            $vaultCard['expires_at'] = $expires_at;
            $carddate = $response['req_card_expiry_date'];
            $expdate = str_replace('-','/', $carddate);
            $cc_type = (isset(self::$ccTypeMap[$response['req_card_type']])) ?
                self::$ccTypeMap[$response['req_card_type']]:'';
            $cc_last_four = substr($response['req_card_number'], -4, 4);
            $vaultCard['details'] = $this->convertDetailsToJSON([
            'type' => $cc_type,
            'maskedCC' => $cc_last_four,
            'expirationDate' => $expdate
        ]);
            $vaultCard['public_hash'] = $this->generatePublicHash($vaultCard);
            $CardExits =  $this->paymentCardSaveTokenFactory->create()->getCollection()
                ->addFieldToFilter('public_hash', array("eq" => $vaultCard['public_hash']));
            if (count($CardExits->getData()) == 0) {
                 $this->paymentCardSaveTokenFactory->create()->setData($vaultCard)->save();
            }
        }
       
    }
   
    /**
     * @return string
     */
    public function getExpirationDate($response)
    {
        $carddate = $response['req_card_expiry_date'];
        $dateArray = explode('-', $carddate);
        $month = $dateArray[0];
        $year  = $dateArray[1];
        $expDate = new \DateTime(
            $year
            . '-'
            . $month
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
        $expDate->add(new \DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }
     /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    public function convertDetailsToJSON($details)
    {
        $json = \Zend_Json::encode($details);
        return $json ? $json : '{}';
    }

    public function generatePublicHash($vaultCard) {
        $hashKey = $vaultCard['gateway_token'];
        if ($vaultCard['customer_id']) {
            $hashKey = $vaultCard['customer_id'];
        }

        $hashKey .= $vaultCard['payment_method_code']
                . $vaultCard['type']
                . $vaultCard['details'];

        return $this->encryptor->getHash($hashKey);
    }
}
