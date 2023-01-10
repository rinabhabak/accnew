<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Cybersource
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Cybersource\Model;

class CustomerManagement
{
    protected $checkoutSession;
    protected $orderRepository;
    protected $soapmodel;
    protected $cybersourcePayment;
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
         \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
         \Magedelight\Cybersource\Model\Api\Soap $soapmodel,
         \Magedelight\Cybersource\Model\Payment $cybersourcePayment
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->soapmodel = $soapmodel;
        $this->cybersourcePayment = $cybersourcePayment;
    }
    public function afterCreate(\Magento\Sales\Model\Order\CustomerManagement $customer, $customerModel)
    {
        $orderId = $this->checkoutSession->getLastOrderId();
        $order = $this->orderRepository->get($orderId);
        if ($order->getPayment()->getMethodInstance()->getCode() == 'magedelight_cybersource') {
            $saveCard = $this->checkoutSession->getSaveCardFlag();
            if ($saveCard == 'true') {
                $payment = $order->getPayment();
                $transactionId = $payment->getdata('last_trans_id');
                $customerid = $order->getCustomerId();
                if (!empty($transactionId) && $customerid) {
                    $profileResponse = $this->soapmodel
                    ->createCustomerProfileFromTransaction($transactionId);
                    $code = $profileResponse->reasonCode;
                    $profileResponsecheck = $profileResponse->paySubscriptionCreateReply->reasonCode;
                    if ($code == '100' && $profileResponsecheck == '100') {
                        $saveData = $this->cybersourcePayment->saveCustomerProfileData($profileResponse, $payment, $customerid);
                    } else {
                        $errorMessage = $this->_errorMessage[$code];
                        if ($code == '102' || $code == '101') {
                            $errorDescription = $profileResponse->invalidField;
                            $errorDescription = is_array($errorDescription) ? implode(',', $errorDescription) : $errorDescription;
                            $errorDescription = $errorDescription.' , '.$this->_errorMessage[$code];
                        }
                        if (isset($errorDescription) && !empty($errorDescription)) {
                            throw new \Magento\Framework\Exception\AlreadyExistsException(__('Error code: '.$code.' : '.$errorDescription));
                        } else {
                            throw new \Magento\Framework\Exception\AlreadyExistsException(__('Error code: '.$code.' : '.$errorMessage));
                        }
                    }
                }
            }
        }
    }
}
