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

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\UrlInterface;

/**
 * Class MerchantDataBuilder
 */
class MerchantDataBuilder implements BuilderInterface
{
    const ACCESS_KEY = 'access_key';

    const PROFILE_ID = 'profile_id';
    
    const OVERRIDE_CUSTOM_RECEIPT_PAGE = 'override_custom_receipt_page';

    const CALLBACK_URL = 'callbackurl';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     *
     * @var UrlInterface 
     */
    private $urlBuilder;
    
    /**
     * 
     * @param ConfigInterface $config
     * @param UrlInterface $urlBuilder
     */
    public function __construct(ConfigInterface $config,UrlInterface $urlBuilder)
    {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        return [
            self::ACCESS_KEY => $this->config->getValue(
                self::ACCESS_KEY,
                $paymentDO->getOrder()->getStoreId()
            ),
            self::PROFILE_ID => $this->config->getValue(
                self::PROFILE_ID,
                $paymentDO->getOrder()->getStoreId()
            ),
            self::OVERRIDE_CUSTOM_RECEIPT_PAGE => $this->config->getValue(
                self::CALLBACK_URL,
                $paymentDO->getOrder()->getStoreId()
            )
        ];
    }
}
