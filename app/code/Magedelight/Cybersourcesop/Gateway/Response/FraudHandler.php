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
namespace Magedelight\Cybersourcesop\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magedelight\Cybersourcesop\Gateway\Validator\DecisionValidator;
use Magento\Payment\Model\InfoInterface;

/**
 * Class FraudHandler
 */
abstract class FraudHandler implements HandlerInterface
{
    const RISK_SCORE = 'risk_score';

    const RISK_FACTORS = 'risk_factors';

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if ((string)$response[DecisionValidator::DECISION] !== 'REVIEW') {
            return;
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        $payment = $paymentDO->getPayment();

        if ($this->getRiskScore($response)) {
            $paymentDO->getPayment()
                ->setAdditionalInformation(
                    self::RISK_SCORE,
                    $this->getRiskScore($response)
                );
        }

        if ($this->getRiskFactors($response)) {
            $paymentDO->getPayment()
                ->setAdditionalInformation(
                    self::RISK_FACTORS,
                    $this->getRiskFactors($response)
                );
        }

        $this->setPaymentState($payment);
    }

    /**
     * Returns risk factors form response
     *
     * @param array $response
     * @return null | string
     */
    abstract protected function getRiskFactors(array $response);

    /**
     * Returns risk factors form response
     *
     * @param array $response
     * @return null | string
     */
    abstract protected function getRiskScore(array $response);

    /**
     * Sets payment state
     *
     * @param InfoInterface $payment
     * @return void
     */
    abstract protected function setPaymentState(InfoInterface $payment);
}
