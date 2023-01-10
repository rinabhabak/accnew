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
namespace Magedelight\Cybersourcesop\Gateway\Response\Soap;

use Magedelight\Cybersourcesop\Observer\DataAssignObserver;
use Magedelight\Cybersourcesop\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magedelight\Cybersourcesop\Gateway\Request\SilentOrder\CcDataBuilder;

/**
 * Payment Details Handler
 */
class PaymentDetailsHandler implements HandlerInterface
{
    const AMOUNT = 'amount';

    const AUTHORIZATIONCODE = 'authorizationCode';

    const AVSCODE = 'avsCode';

    const AVSCODERAW = 'avsCodeRaw';

    const MERCHANTREFCODE = 'merchantReferenceCode';

    /**
     * List of additional details
     * @var array
     */
    protected $additionalInformationMapping = [
        self::AMOUNT,
        self::AUTHORIZATIONCODE,
        self::AVSCODE,
        self::AVSCODERAW
    ];

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader,PaymentTokenManagementInterface $tokenManagement)
    {
        $this->subjectReader = $subjectReader;
        $this->tokenManagement = $tokenManagement;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();
        $payment->setCcTransId($response['requestID']);
        $payment->setLastTransId($response['requestID']);
        $order = $paymentDO->getOrder();
        //remove previously set payment nonce
        $public_hash = $payment->getAdditionalInformation(DataAssignObserver::PUBLIC_HASH);
        $customerId = $order->getCustomerId();
            $paymentToken = $this->tokenManagement->getByPublicHash($public_hash, $customerId);
            if ($paymentToken) {
                $tokenDetails = json_decode($paymentToken->getDetails(),true);
                $payment->setAdditionalInformation(
                        DataAssignObserver::CC_LAST_4,
                        $tokenDetails['maskedCC']
                    );
                $payment->setAdditionalInformation(
                        DataAssignObserver::CC_TYPE,
                        $tokenDetails['type']
                    );
                $payment->setCcLast4($tokenDetails['maskedCC']);

                $expirationDate = str_replace('-','/', $tokenDetails['expirationDate']);
                $dateArray = explode('/', $expirationDate);
                
                $month = $dateArray[0];
                $year = $dateArray[1];
                $payment->setCcExpMonth($month);
                $payment->setCcExpYear($year);
         }
        $payment->setLastTransId($response['requestID']);
        $this->prepareAdditionalInfo($payment,$response);
    }
    /**
     * prepare additional information
     * @param type $response
     */
    private function prepareAdditionalInfo($payment,$response)
    {
        foreach ($this->additionalInformationMapping as $item) {
            if (!isset($response['ccAuthReply'][$item])) {
                continue;
            }
            $payment->setAdditionalInformation($item, $response['ccAuthReply'][$item]);
        }
        if(isset($response['merchantReferenceCode']))
        {
            $payment
                ->setAdditionalInformation(
                    self::MERCHANTREFCODE,
                    $response['merchantReferenceCode']
                );
        }
       
    }
}
