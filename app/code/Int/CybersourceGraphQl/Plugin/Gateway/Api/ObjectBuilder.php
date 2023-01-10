<?php

namespace Int\CybersourceGraphQl\Plugin\Gateway\Api;

class ObjectBuilder
{
    public function afterGetOrderItem(
        \ParadoxLabs\CyberSource\Gateway\Api\ObjectBuilder $subject,
        $result,
        \Magento\Sales\Model\Order\Item $orderItem,
        $lineNumber
    )
    {
        $result->setUnitPrice($orderItem->getBasePrice());
        $result->setTaxAmount($orderItem->getBaseTaxAmount()); // NB: Row total tax
        return $result;
    }
}