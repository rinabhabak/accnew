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

use Magedelight\Cybersourcesop\Gateway\Helper\SilentOrderHelper;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SignCompositeDecorator
 */
class SignCompositeDecorator implements BuilderInterface
{
    const SIGNED_FIELD_NAMES = 'signed_field_names';

    const UNSIGNED_FIELD_NAMES = 'unsigned_field_names';

    const SIGNED_DATE_TIME = 'signed_date_time';

    const SIGNATURE = 'signature';

    const SIGNED_DATE_TIME_FORMAT = "Y-m-d\TH:i:s\Z";

    /**
     * Unsigned fields
     *
     * @var array
     */
    static private $unsignedFields = [
        CcDataBuilder::CARD_TYPE,
        CcDataBuilder::CARD_NUMBER,
        CcDataBuilder::CARD_EXPIRY_DATE,
        CcDataBuilder::CARD_CVN
    ];

    /**
     * @var \Magento\Framework\Intl\DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var BuilderInterface[] | TMap
     */
    private $builders;

    /**
     * @param \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
     * @param ConfigInterface $config
     * @param array $builders
     * @param TMapFactory $tmapFactory
     */
    public function __construct(
        DateTimeFactory $dateTimeFactory,
        ConfigInterface $config,
        array $builders,
        TMapFactory $tmapFactory
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->config = $config;

        $this->builders = $tmapFactory->create(
            [
                'array' => $builders,
                'type' => 'Magento\Payment\Gateway\Request\BuilderInterface'
            ]
        );
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
        $signedFields = [];
        $unsignedFields = [];

        $result = [];
        foreach ($this->builders as $builder) {
            // @TODO implement exceptions catching
            $result = array_merge($result, $builder->build($buildSubject));
        }

        foreach ($result as $field => $value) {
            if (in_array($field, self::$unsignedFields)) {
                $unsignedFields[$field] = $value;
                continue;
            }
            $signedFields[$field] = $value;
        }

        $dateTime = $this->dateTimeFactory->create('now', new \DateTimeZone('GMT'));

        $signedFields[self::SIGNED_DATE_TIME] = $dateTime->format(self::SIGNED_DATE_TIME_FORMAT);

        if ($unsignedFields) {
            $signedFields[self::UNSIGNED_FIELD_NAMES] = implode(',', array_keys($unsignedFields));
        }

        $signedFields[self::SIGNED_FIELD_NAMES] = '';
        $signedFields[self::SIGNED_FIELD_NAMES] = implode(',', array_keys($signedFields));

        $result = array_merge($signedFields, $unsignedFields);

        $paymentDO = SubjectReader::readPayment($buildSubject);
        
        $result[self::SIGNATURE] =  SilentOrderHelper::signFields(
            $signedFields,
            $this->config->getValue('secret_key', $paymentDO->getOrder()->getStoreId())
        );

        return $result;
    }
}
