<?php

namespace Int\CybersourceGraphQl\Model\Service\CardinalCruise;

class JsonWebTokenGenerator extends \ParadoxLabs\CyberSource\Model\Service\CardinalCruise\JsonWebTokenGenerator
{
    /**
     * Get the items payload for the given quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    protected function getPayloadItems(\Magento\Quote\Model\Quote $quote)
    {
        $items = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            $items[] = [
                'Name' => substr((string)$item->getName(), 0, 128),
                'SKU' => substr((string)$item->getSku(), 0, 20),
                'Quantity' => $item->getQty(),
                'Price' => (float)$item->getBasePrice(),
            ];
        }

        return $items;
    }
}