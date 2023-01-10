<?php

namespace Int\CybersourceGraphQl\Model;

class Gateway extends \ParadoxLabs\CyberSource\Model\Gateway
{
    /**
     * Run the given request via SOAP API.
     *
     * @param \ParadoxLabs\CyberSource\Gateway\Api\RequestMessage $requestMessage
     * @param bool $log
     * @return \ParadoxLabs\CyberSource\Gateway\Api\ReplyMessage
     * @throws \Magento\Framework\Exception\RuntimeException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function run(\ParadoxLabs\CyberSource\Gateway\Api\RequestMessage $requestMessage, $log = true)
    {
        if ($this->soapClient instanceof \ParadoxLabs\CyberSource\Gateway\Api\TransactionProcessor === false) {
            throw new \Magento\Framework\Exception\StateException(__('CyberSource gateway has not been initialized'));
        }

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cyber-source-custom.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);



        try {
            $logger->info('-- Payment Request --');
            $logger->info(print_r($requestMessage, true));

            $reply = $this->soapClient->runTransaction($requestMessage);

            $logger->info('-- Payment Responce --');
            $logger->info(print_r($reply, true));
        } catch (\Exception $exception) {

            $logger->info(' -- Payment Exception ---');
            $logger->info(print_r($exception->getMessage(), true));

            if ($log === true) {
                $this->helper->log(
                    $this->code,
                    sprintf('CyberSource Gateway error: %s', trim((string)$exception->getMessage()))
                );
            }

            throw new \Magento\Framework\Exception\RuntimeException(
                __('CyberSource Gateway error: %1', trim((string)$exception->getMessage())),
                $exception
            );
        } finally {
            $logger->info(' -- Responce Soap ---');
            $logger->info(print_r($this->soapClient->__getLastResponse(), true));

            $response = $this->sanitizeLog($this->soapClient->__getLastResponse());

            if ($this->config->isSandboxMode()) {
                $request = $this->sanitizeLog($this->soapClient->__getLastRequest());

                $this->helper->log(
                    $this->code,
                    'REQUEST: ' . $request . "\nRESPONSE: " . $response,
                    true
                );
            }

            if ($log === true) {
                $this->helper->log($this->code, 'RESPONSE: ' . $response);
            }

            // Parse response into array for easier handling
            $this->lastResponse = $this->xmlToArray($this->soapClient->__getLastResponse());
            $this->helper->log(
                $this->code,
                'RESPONSE: ' . json_encode($this->lastResponse),
                true
            );
        }

        return $reply;
    }

    /**
     * Run an auth transaction for $amount with the given payment info
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        /** @var \Magento\Sales\Model\Order $order */
        $order  = $payment->getOrder();

        $purchaseTotals = $this->getOrderPurchaseTotals($payment, $order, $amount);

        $request = $this->createRequest();
        $request->setMerchantReferenceCode($order->getIncrementId());
        $request->setDeviceFingerprintID($this->config->getFingerprintSessionId($order->getQuoteId(), null, true));
        $request->setBillTo($this->objectBuilder->getOrderBillTo($order));
        $request->setItem($this->objectBuilder->getOrderItems($this->lineItems));

        $card = $this->getCard();

        if(!$card || !$card->getPaymentId() || $card->getPaymentId() == ""){
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cyber-source-custom.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            throw new \Magento\Payment\Gateway\Command\CommandException(__('Your payment token has been expired. Please try again.'), null, 203);
        }

        $request->setRecurringSubscriptionInfo($this->objectBuilder->getTokenInfo($this->getCard()));
        $request->setPurchaseTotals($purchaseTotals);
        $request->setCcAuthService(
            $this->objectBuilder->getAuthService(
                $this->helper->getIsFrontend() ? 'internet' : 'moto'
            )
        );

        if ((bool)$order->getIsVirtual() === false) {
            $request->setShipTo($this->objectBuilder->getOrderShipTo($order));
        }

        if (!empty($payment->getData('cc_cid'))) {
            $request->setCard($this->objectBuilder->getCardForCvn($payment->getData('cc_cid')));
        } else {
            $request->setBusinessRules($this->objectBuilder->getBusinessRules(true));
        }

        // If this is a follow-on transaction (some amount already captured), do not run decision manager again.
        if ($payment->getAmountPaid() > 0) {
            $request->setDecisionManager($this->objectBuilder->enableDecisionManager(false));
        }

        $this->requestPayerAuthentication($payment, $request);

        $reply = $this->run($request);

        return $this->interpretTransaction($reply, $payment);
    }

    /**
     * Run a refund transaction for $amount with the given payment info
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @param string $transactionId
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount, $transactionId = null)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        /** @var \Magento\Sales\Model\Order $order */
        $order  = $payment->getOrder();

        $purchaseTotals = $this->objectBuilder->getPurchaseTotals($order->getBaseCurrencyCode(), $amount);
        if ($payment->getCreditmemo() instanceof \Magento\Sales\Api\Data\CreditmemoInterface) {
            if ($payment->getCreditmemo()->getTaxAmount()) {
                $purchaseTotals->setTaxAmount($payment->getCreditmemo()->getBaseTaxAmount());
            }
            if ($payment->getCreditmemo()->getShippingAmount()) {
                $purchaseTotals->setShippingAmount($payment->getCreditmemo()->getBaseShippingAmount());
            }
        }

        $ccCreditService = $this->objectBuilder->getCreditService(
            'internet',
            $transactionId !== null ? $transactionId : $this->getTransactionId()
        );

        $request = $this->createRequest();
        $request->setMerchantReferenceCode($order->getIncrementId());
        $request->setPurchaseTotals($purchaseTotals);
        $request->setCcCreditService($ccCreditService);

        $reply = $this->run($request);

        try {
            return $this->interpretTransaction($reply, $payment);
        } catch (\Exception $exception) {
            // Handle 'not valid for follow-on transaction' error (past allowed period).
            if ($exception->getCode() === 241) {
                $this->helper->log($this->code, 'Transaction not refundable. Attempting unlinked credit.');

                $this->setTransactionId(null)
                    ->setCard($this->getData('card'));

                return $this->refund($payment, $amount, '');
            }

            // Pass any other errors through.
            throw $exception;
        }
    }

    /**
     * Run a void transaction for the given payment info
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $transactionId
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment, $transactionId = null)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        /** @var \Magento\Sales\Model\Order $order */
        $order  = $payment->getOrder();

        $request = $this->createRequest();
        $request->setMerchantReferenceCode($order->getIncrementId());

        if ($order->getTotalDue() > 0) {
            $purchaseTotals = $this->objectBuilder->getPurchaseTotals(
                $order->getBaseCurrencyCode(),
                $order->getBaseTotalDue() ?: $order->getBaseTotalPaid()
            );

            $ccAuthReversalService = $this->objectBuilder->getAuthReversalService(
                $transactionId ?: $this->getTransactionId()
            );

            $request->setPurchaseTotals($purchaseTotals);
            $request->setCcAuthReversalService($ccAuthReversalService);
        } else {
            $purchaseTotals = $this->objectBuilder->getPurchaseTotals(
                $order->getBaseCurrencyCode(),
                $order->getBaseTotalPaid()
            );

            $voidService = $this->objectBuilder->getVoidService(
                $transactionId ?: $this->getTransactionId()
            );

            $request->setPurchaseTotals($purchaseTotals);
            $request->setVoidService($voidService);
        }

        $reply = $this->run($request);

        return $this->interpretTransaction($reply, $payment);
    }
}