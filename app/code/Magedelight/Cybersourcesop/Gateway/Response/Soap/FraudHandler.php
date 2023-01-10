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

use Magento\Payment\Model\InfoInterface;

/**
 * Class FraudHandler
 */
class FraudHandler extends \Magedelight\Cybersourcesop\Gateway\Response\FraudHandler
{
    /**
     * Returns risk factors form response
     *
     * @param array $response
     * @return null | string
     */
    protected function getRiskFactors(array $response)
    {
        return isset($response['afsReply']['afsFactorCode'])
            ? $response['afsReply']['afsFactorCode']
            : null;
    }

    /**
     * Returns risk factors form response
     *
     * @param array $response
     * @return null | string
     */
    protected function getRiskScore(array $response)
    {
        return isset($response['afsReply']['afsResult'])
            ? $response['afsReply']['afsResult']
            : null;
    }

    /**
     * Sets payment state
     *
     * @param InfoInterface $payment
     * @return void
     */
    protected function setPaymentState(InfoInterface $payment)
    {
        $payment->setIsTransactionPending(true);
        $payment->setIsFraudDetected(true);
    }
}
