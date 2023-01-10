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

use Magento\Payment\Gateway\Command\Result\ArrayResult;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\Logger;

class CreateCommand implements CommandInterface
{
    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @var ArrayResultFactory
     */
    private $arrayResultFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param BuilderInterface $builder
     * @param ArrayResultFactory $arrayResultFactory
     * @param Logger $logger
     */
    public function __construct(
        BuilderInterface $builder,
        ArrayResultFactory $arrayResultFactory,
        Logger $logger
    ) {
        $this->builder = $builder;
        $this->arrayResultFactory = $arrayResultFactory;
        $this->logger = $logger;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return ArrayResult
     * @throws LocalizedException
     */
    public function execute(array $commandSubject)
    {
        $result = $this->builder->build($commandSubject);
        $this->logger->debug(['payment_token_request' => $result]);
        return $this->arrayResultFactory->create(['array' => $result]);
    }
}
