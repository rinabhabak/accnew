<?php

namespace Int\CybersourceGraphQl\Plugin\Model\Service\CardinalCruise;

class EnrollmentParams
{
    public function beforePopulateEnrollmentService(
        \ParadoxLabs\CyberSource\Model\Service\CardinalCruise\EnrollmentParams $subject,
        \ParadoxLabs\CyberSource\Gateway\Api\PayerAuthEnrollService $enrollService,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \ParadoxLabs\TokenBase\Api\Data\CardInterface $card
    )
    {
        if ($card->getAdditional('cc_type') === 'DI' || $card->getAdditional('cc_type') === 'AE') {
            $enrollService->setRun('false');
        }
    }
}