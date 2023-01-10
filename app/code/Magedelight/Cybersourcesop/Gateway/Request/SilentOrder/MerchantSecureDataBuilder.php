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

use Magento\Framework\Config\ScopeInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class MerchantSecureDataBuilder
 */
class MerchantSecureDataBuilder implements BuilderInterface
{
    const MERCHANT_SECURE_DATA1 = 'merchant_secure_data1';

    const MERCHANT_SECURE_DATA2 = 'merchant_secure_data2';

    const MERCHANT_SECURE_DATA3 = 'merchant_secure_data3';

    /**
     * @var ScopeInterface
     */
    private $scope;

    /**
     * @param ScopeInterface $scope
     */
    public function __construct(ScopeInterface $scope)
    {
        $this->scope = $scope;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        return [
            static::MERCHANT_SECURE_DATA1 => $paymentDO->getOrder()->getId(),
            static::MERCHANT_SECURE_DATA2 => $paymentDO->getOrder()->getStoreId(),
            static::MERCHANT_SECURE_DATA3 => $this->scope->getCurrentScope()
        ];
    }
}
