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
class ItemInfoDataBuilder implements BuilderInterface
{
    /**
     * Item block name
     */
    const ITEM = 'item';

    /**
     * Unit Price block name
     */
    const UNITPRICE = 'unitPrice';

    /**
     * tax amount value.
     */
    const TAXAMOUNT = 'taxAmount';
    /**
     * quantity value
     */
    const QUANTITY = 'quantity';

    /**
     * product name
     */
    const PRODUCTNAME = 'productName';

    /**
     * Product Sku
     */
    const PRODUCTSKU = 'productSKU';

    /**
     * Increament id
     */
    const ID = 'id';
   
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

        $order = $paymentDO->getPayment()->getOrder();
        $result = [];
        if ($order instanceof \Magento\Sales\Model\Order) {
            $i = 0;
            foreach ($order->getAllVisibleItems() as $_item) {
                 $result[self::ITEM][$i] = [
                     self::ID => $i,
                     self::UNITPRICE => round($_item->getBasePrice(), 2),
                     self::TAXAMOUNT => round($_item->getData('tax_amount'), 2),
                     self::QUANTITY => (int) $_item->getQtyOrdered(),
                     self::PRODUCTNAME => substr($_item->getName(), 0, 30),
                     self::PRODUCTSKU => $_item->getSku()
                     ];
                    ++$i;
               }
        }
        return $result;
    }
}
