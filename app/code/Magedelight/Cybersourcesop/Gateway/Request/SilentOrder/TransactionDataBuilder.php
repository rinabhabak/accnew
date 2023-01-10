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
namespace Magedelight\Cybersourcesop\Gateway\Request\SilentOrder;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Math\Random;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\InfoInterface;

/**
 * Class TransactionDataBuilder
 */
class TransactionDataBuilder implements BuilderInterface
{
    const TRANSACTION_UUID = 'transaction_uuid';

    const TRANSACTION_TYPE = 'transaction_type';

    const REFERENCE_NUMBER = 'reference_number';

    const AMOUNT = 'amount';

    const CURRENCY = 'currency';

    const LOCALE = 'locale';

    const RANDOM_LENGTH = 30;

    /**
     * @var string
     */
    private $transactionType;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param Random $random
     * @param ResolverInterface $localeResolver
     * @param string $transactionType
     */
    public function __construct(
        Random $random,
        ResolverInterface $localeResolver,
        $transactionType
    ) {
        $this->transactionType = $transactionType;
        $this->random = $random;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws \InvalidArgumentException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        return [
            self::TRANSACTION_UUID => $this->random->getRandomString(
                self::RANDOM_LENGTH,
                Random::CHARS_DIGITS
            ),
            self::REFERENCE_NUMBER => $this->getReferenceNumber($paymentDO->getPayment()),
            self::TRANSACTION_TYPE => $this->transactionType,
            self::AMOUNT => sprintf('%.2F', SubjectReader::readAmount($buildSubject)),
            self::CURRENCY => $paymentDO->getOrder()->getCurrencyCode(),
            self::LOCALE => substr($this->localeResolver->getLocale(), 0, 2)
        ];
    }

    /**
     * Returns reference number
     *
     * @param InfoInterface $payment
     * @return string
     */
    private function getReferenceNumber(InfoInterface $payment)
    {
        if ($payment->getAdditionalInformation(self::REFERENCE_NUMBER)) {
            return $payment->getAdditionalInformation(self::REFERENCE_NUMBER);
        }

        return $this->random->getRandomString(self::RANDOM_LENGTH, Random::CHARS_DIGITS);
    }
}
