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
namespace Magedelight\Cybersourcesop\Gateway\Validator\SilentOrder;

use Magedelight\Cybersourcesop\Gateway\Helper\SilentOrderHelper;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class SignValidator extends AbstractValidator
{
    /**
     * Signed fields key
     */
    const SIGNED_FIELD_NAMES = 'signed_field_names';

    /**
     * Signature field
     */
    const SIGNATURE = 'signature';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ConfigInterface $config
    ) {
        parent::__construct($resultFactory);

        $this->config = $config;
    }

    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return null|ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        $paymentDO = SubjectReader::readPayment($validationSubject);

        if (!isset(
            $response[static::SIGNED_FIELD_NAMES],
            $response[static::SIGNATURE])
        ) {
            return $this->createResult(false, [__('Gateway validation error')]);
        }

        try {
            return $this->createResult(
                SilentOrderHelper::signFields(
                    $this->getFieldsToSign(
                        $response,
                        $response[static::SIGNED_FIELD_NAMES]
                    ),
                    $this->config->getValue(
                        'secret_key',
                        $paymentDO->getOrder()->getStoreId()
                    )
                ) === $response[static::SIGNATURE]
            );
        } catch (\LogicException $e) {
            return $this->createResult(false, [__('Gateway validation error')]);
        }
    }

    /**
     * Returns signed fields
     *
     * @param array $response
     * @param string $signedList
     * @return array
     */
    private function getFieldsToSign(array $response, $signedList)
    {
        $result = [];
        foreach (explode(',', $signedList) as $key) {
            if (!isset($response[$key])) {
                throw new \LogicException;
            }
            $result[$key] = $response[$key];
        }
        return $result;
    }
}
