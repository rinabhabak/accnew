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
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class CcDataBuilder
 */
class CcDataBuilder implements BuilderInterface
{
    const CARD_TYPE = 'card_type';

    const CARD_NUMBER = 'card_number';

    const CARD_EXPIRY_DATE = 'card_expiry_date';

    const CARD_CVN = 'card_cvn';

    const PAYMENT_METHOD = 'payment_method';

    /**
     * Map for CC type field. Magento scope => Cybersource scope
     *
     * @var array
     */
    static private $ccTypeMap = [
        'AE' => '003',
        'VI' => '001',
        'MC' => '002',
        'DI' => '004',
        'DN' => '005',
        'JCB' => '007',
        'MD' => '024',
        'MI' => '042'
    ];

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject)
    {
        if (!isset(
            $buildSubject['cc_type'], self::$ccTypeMap[$buildSubject['cc_type']]
        )) {
            throw new LocalizedException(__('CC type field should be provided'));
        }
        return [
            self::CARD_TYPE => self::$ccTypeMap[$buildSubject['cc_type']],
            self::CARD_NUMBER => '',
            self::CARD_EXPIRY_DATE => '',
            self::CARD_CVN => '',
            self::PAYMENT_METHOD => 'card'
        ];
    }
}
