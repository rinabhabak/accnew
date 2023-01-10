<?php

namespace Int\CybersourceGraphQl\Plugin;

class QuotePaymentPlugin
{
    /**
     * @param \Magento\Quote\Model\Quote\Payment $subject
     * @param array $data
     * @return array
     */
    public function beforeSetAdditionalData(
        \Magento\Quote\Model\Quote\Payment $subject,
        $additionalData
    ) {
        // add your extra data here....
        $additionalData['aaaa']='789';
    }
}