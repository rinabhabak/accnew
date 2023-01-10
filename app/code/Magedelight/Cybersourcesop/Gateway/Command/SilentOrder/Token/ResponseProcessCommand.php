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
namespace Magedelight\Cybersourcesop\Gateway\Command\SilentOrder\Token;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\PaymentMethodManagementInterface;

class ResponseProcessCommand implements CommandInterface
{
    /**
     * @var HandlerInterface
     */
    private $handlerInterface;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentManagement;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param ValidatorInterface $validator
     * @param HandlerInterface $handler
     * @param PaymentMethodManagementInterface $paymentManagement
     * @param Logger $logger
     */
    public function __construct(
        ValidatorInterface $validator,
        HandlerInterface $handler,
        PaymentMethodManagementInterface $paymentManagement,
        Logger $logger
    ) {
        $this->handlerInterface = $handler;
        $this->validator = $validator;
        $this->paymentManagement = $paymentManagement;
        $this->logger = $logger;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return void
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function execute(array $commandSubject)
    {
        $response = SubjectReader::readResponse($commandSubject);
        
        
        $this->logger->debug(['payment_token_response' => $response]);

        $result = $this->validator->validate($commandSubject);
        if (!$result->isValid()) {
            throw new \LogicException();
        }

        $this->handlerInterface->handle($commandSubject, $response);

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($commandSubject);

        $this->paymentManagement->set(
            $paymentDO->getOrder()->getId(),
            $paymentDO->getPayment()
        );
    }
}
