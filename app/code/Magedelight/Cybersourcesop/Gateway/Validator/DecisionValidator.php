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
namespace Magedelight\Cybersourcesop\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class DecisionValidator extends AbstractValidator
{
    const DECISION = 'decision';

    const REASON_CODE = 'reasonCode';

    /**
     *  Successful transaction.
     */
    const REASON_SUCCESSFUL = 100;
    /**
     *  The authorization has already been reversed.
     */
    const REASON_AUTH_REVERSED = 237;

    /**
     * The transaction has already been settled or reversed.
     */
    const REASON_TRANSACTION_REVERSED_SETTLED = 243;

    /**
     * List of acceptable decisions. May be configured value
     *
     * @var array
     */
    private static $acceptableDecisions = [
        'ACCEPT',
        'REVIEW'
    ];

    /**
     * List of acceptable reason codes. May be configured value
     *
     * @var array
     */
    private static $acceptableReasonCodes = [
        self::REASON_SUCCESSFUL,
        self::REASON_AUTH_REVERSED,
        self::REASON_TRANSACTION_REVERSED_SETTLED
    ];

    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return null|ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        if (!isset($response[static::DECISION])) {
            return $this->createResult(false, [__('Your payment has been declined. Please try again.')]);
        }

        $result = $this->createResult(
            in_array(
                $response[static::DECISION],
                self::$acceptableDecisions
            ) ||
            in_array(
                $response[static::REASON_CODE],
                self::$acceptableReasonCodes
            ),
            [__('Your payment has been declined. Please try again.')]
        );

        return $result;
    }
}
