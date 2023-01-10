<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\Cybersource\Model;

use Magento\Payment\Model\CcGenericConfigProvider;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;

/**
 * Class DataProvider.
 */
class ConfigProvider extends CcGenericConfigProvider
{
    protected $methodCodes = [
        Payment::CODE,
    ];

    protected $cards;
    protected $encryptor;
    protected $config;

    protected $checkoutSession;

    protected $customerSession;

    protected $dataHelper;

    protected $urlBuilder;
    protected $cardfactory;
    protected $sessionquote;
    protected $_paymentConfig;

    public function __construct(
            CcConfig $ccConfig,
            PaymentHelper $paymentHelper,
            \Magedelight\Cybersource\Model\Config $config,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\Url $urlBuilder,
            \Magento\Payment\Model\Config $paymentConfig,
            \Magento\Backend\Model\Session\Quote $sessionquote,
            \Magedelight\Cybersource\Helper\Data $dataHelper,
            \Magento\Framework\Encryption\Encryptor $encryptor,
            CardsFactory $cardFactory,

            array $methodCodes = []
    ) {
        parent::__construct($ccConfig, $paymentHelper, $methodCodes);
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
        $this->_paymentConfig = $paymentConfig;
        $this->dataHelper = $dataHelper;
        $this->encryptor = $encryptor;
        $this->cardfactory = $cardFactory;
        $this->sessionquote = $sessionquote;
    }

    /**
     * Returns applicable stored cards.
     *
     * @return array
     */
    public function getStoredCards()
    {
        $result = array();
        $cardData = [];
        $websiteId = $this->dataHelper->getWebsiteId();
        $customer_account_scope =  $this->dataHelper->getCustomerAccountScope();
        
        
        if ($this->dataHelper->checkAdmin()) {
            $customerId = $this->sessionquote->getQuote()->getCustomerId();
        } else {
            $customer = $this->customerSession->getCustomer();
            $customerId = $customer->getId();
        }
        if (!empty($customerId)) {
            $cardModel = $this->cardfactory->create();
            $cardData = $cardModel->getCollection()->addFieldToFilter('customer_id', $customerId);
            
            if ($customer_account_scope) {
               $cardData->addFieldToFilter('website_id', $websiteId);
            }
            
            $cardData->getData();
        }

        foreach ($cardData as $key => $_card) {
            $cardReplaced = 'XXXX-'.$_card['cc_last_4'];
            $result[$this->encryptor->encrypt($_card['subscription_id'])] = sprintf('%s, %s %s', $cardReplaced, $_card['firstname'], $_card['lastname']);
        }
        $result['new'] = 'Use other card';

        return $result;
    }

    protected function getCcAvailableCcTypes()
    {
        return $this->dataHelper->getCcAvailableCardTypes();
    }

    public function canSaveCard()
    {
        if (!$this->config->getSaveCardOptional()) {
            return true;
        }

        return false;
    }
    public function getCcMonths()
    {
        return $this->_paymentConfig->getMonths();
    }

    public function show3dSecure()
    {
        return false;
    }

    public function getConfig()
    {
        if (!$this->config->getIsActive()) {
            return [];
        }
        $config = array_merge_recursive([
            'payment' => [
                \Magedelight\Cybersource\Model\Payment::CODE => [
                   'canSaveCard' => $this->canSaveCard(),
                    'storedCards' => $this->getStoredCards(),
                    'ccMonths' => $this->getCcMonths(),
                    'ccYears' => $this->getCcYears(),
                    'hasVerification' => $this->config->isCardVerificationEnabled(),
                    'creditCardExpMonth' => (int) $this->dataHelper->getTodayMonth(),
                    'creditCardExpYear' => (int) $this->dataHelper->getTodayYear(),
                    'availableCardTypes' => $this->getCcAvailableCcTypes(),
                ],
            ],
        ]);
        return $config;
    }
}
