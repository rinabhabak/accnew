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
namespace Magedelight\Cybersourcesop\Gateway\Request\Soap;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magedelight\Cybersourcesop\Gateway\Helper\SubjectReader;

/**
 * Class AddressDataBuilder
 */
class AddressDataBuilder implements BuilderInterface
{
    /**
     * ShippingAddress block name
     */
    const SHIPPING_ADDRESS = 'shipTo';

    /**
     * BillingAddress block name
     */
    const BILLING_ADDRESS = 'billTo';

    /**
     * The first name value must be less than or equal to 255 characters.
     */
    const FIRST_NAME = 'firstName';
    /**
     * The email value must be less than or equal to 255 characters.
     */
    const EMAIL = 'email';

    /**
     * The last name value must be less than or equal to 255 characters.
     */
    const LAST_NAME = 'lastName';

    /**
     * The customer’s company. 255 character maximum.
     */
    const COMPANY = 'company';

    /**
     * The street address. Maximum 255 characters, and must contain at least 1 digit.
     * Required when AVS rules are configured to require street address.
     */
    const STREET_ADDRESS = 'street1';

    /**
     * The extended address information—such as apartment or suite number. 255 character maximum.
     */
    const EXTENDED_ADDRESS = 'street2';

    /**
     * The locality/city. 255 character maximum.
     */
    const CITY = 'city';

    /**
     * The state or province. For PayPal addresses, the region must be a 2-letter abbreviation;
     * for all other payment methods, it must be less than or equal to 255 characters.
     */
    const REGION = 'state';

    /**
     * The postal code. Postal code must be a string of 5 or 9 alphanumeric digits,
     * optionally separated by a dash or a space. Spaces, hyphens,
     * and all other special characters are ignored.
     */
    const POSTAL_CODE = 'postalCode';

    /**
     * The ISO 3166-1 alpha-2 country code specified in an address.
     * The gateway only accepts specific alpha-2 values.
     *
     */
    const COUNTRY_CODE = 'country';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $result = [];

        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            $result[self::BILLING_ADDRESS] = [
                self::FIRST_NAME => $billingAddress->getFirstname(),
                self::LAST_NAME => $billingAddress->getLastname(),
                self::EMAIL => $billingAddress->getEmail(),
                self::COMPANY => $billingAddress->getCompany(),
                self::STREET_ADDRESS => $billingAddress->getStreetLine1(),
                self::EXTENDED_ADDRESS => $billingAddress->getStreetLine2(),
                self::CITY => $billingAddress->getCity(),
                self::REGION => $billingAddress->getRegionCode(),
                self::POSTAL_CODE => $billingAddress->getPostcode(),
                self::COUNTRY_CODE => $billingAddress->getCountryId()
            ];
        }

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $result[self::SHIPPING_ADDRESS] = [
                self::FIRST_NAME => $shippingAddress->getFirstname(),
                self::LAST_NAME => $shippingAddress->getLastname(),
                 self::EMAIL => $billingAddress->getEmail(),
                self::COMPANY => $shippingAddress->getCompany(),
                self::STREET_ADDRESS => $shippingAddress->getStreetLine1(),
                self::EXTENDED_ADDRESS => $shippingAddress->getStreetLine2(),
                self::CITY => $shippingAddress->getCity(),
                self::REGION => $shippingAddress->getRegionCode(),
                self::POSTAL_CODE => $shippingAddress->getPostcode(),
                self::COUNTRY_CODE => $shippingAddress->getCountryId()
            ];
        }

        return $result;
    }
}
