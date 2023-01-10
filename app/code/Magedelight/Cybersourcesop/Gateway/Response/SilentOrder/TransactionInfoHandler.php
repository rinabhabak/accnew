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

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magedelight\Cybersourcesop\Observer\DataAssignObserver;
use Magedelight\Cybersourcesop\Gateway\Request\SilentOrder\PaymentTokenBuilder;

class TransactionInfoHandler implements HandlerInterface
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
     * Request suffix
     */
    const REQUEST_SUFFIX = 'req_';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     *
     * @var Magento\Vault\Model\PaymentTokenFactory
     */
    protected $paymentCardSaveTokenFactory;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Vault\Model\PaymentTokenFactory $paymentCardSaveTokenFactory)
    {
        $this->config = $config;
        $this->encryptor = $encryptor;
        $this->paymentCardSaveTokenFactory = $paymentCardSaveTokenFactory;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $fieldsToStore = explode(',', $this->config->getValue('paymentInfoKeys'));

        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        foreach ($fieldsToStore as $field) {
            $requestFieldName = null;
            if (isset($response[$field])) {
                $requestFieldName = $field;
            } elseif (isset($response[self::REQUEST_SUFFIX . $field])) {
                $requestFieldName = self::REQUEST_SUFFIX . $field;
            }

            if (!$requestFieldName) {
                continue;
            }

            $paymentDO->getPayment()->setAdditionalInformation(
                $field,
                $response[$requestFieldName]
            );
        }
        /* code for save token */
         $order = $payment->getOrder();
         if($order!=NULL)
         {
             $customerId = $order->getCustomerId();
             $active_token = $payment->getAdditionalInformation(DataAssignObserver::IS_ACTIVE_PAYMENT_TOKEN_ENABLER);
            if($active_token && $customerId!=NULL)
            {
                $vaultCard = [];
                $vaultCard['gateway_token'] = $response[PaymentTokenBuilder::REQ_PAYMENT_TOKEN];
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
         
        /* end code for save token */

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
