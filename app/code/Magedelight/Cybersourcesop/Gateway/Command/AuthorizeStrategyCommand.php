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
namespace Magedelight\Cybersourcesop\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Magedelight\Cybersourcesop\Gateway\Request\SilentOrder\PaymentTokenBuilder;

class AuthorizeStrategyCommand implements CommandInterface
{
    /**
     * Secure Acceptance authorize command name
     */
    const SECURE_ACCEPTANCE_AUTHORIZE = 'secure_acceptance_authorize';

    /**
     * Simple order authorize command name
     */
    const SIMPLE_ORDER_AUTHORIZE = 'simple_order_authorize';

    /**
     * Simple order subscription command name
     */
    const SIMPLE_ORDER_SUBSCRIPTION = 'simple_order_subscription';

    /**
     * @var Command\CommandPoolInterface
     */
    private $commandPool;

    /**
     * @param Command\CommandPoolInterface $commandPool
     */
    public function __construct(
        Command\CommandPoolInterface $commandPool
    ) {
        $this->commandPool = $commandPool;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return null|Command\ResultInterface
     * @throws LocalizedException
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($commandSubject);

        $paymentInfo = $paymentDO->getPayment();

        if ($paymentInfo->getAdditionalInformation(PaymentTokenBuilder::PAYMENT_TOKEN)) {
            return $this->commandPool
                ->get(self::SECURE_ACCEPTANCE_AUTHORIZE)
                ->execute($commandSubject);
        }

        $this->commandPool
            ->get(self::SIMPLE_ORDER_SUBSCRIPTION)
            ->execute($commandSubject);

        return $this->commandPool
            ->get(self::SIMPLE_ORDER_AUTHORIZE)
            ->execute($commandSubject);
    }
}
