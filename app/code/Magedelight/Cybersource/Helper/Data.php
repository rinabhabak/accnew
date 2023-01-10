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
namespace Magedelight\Cybersource\Helper;

use Magento\Payment\Model\Config as PaymentConfig;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_region;
    protected $today = null;
    protected $_addressConfig;
    protected $dateTime;
    protected $cybersourceConfig;
    protected $sessionquote;
    const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';
    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';
    const REQUEST_TYPE_CREDIT = 'CREDIT';
    const REQUEST_TYPE_VOID = 'VOID';
    /**
     * @var PaymentConfig
     */
    protected $paymentConfig;

    protected $_storeManager;


    protected $_errorMessage = array(
        '100' => 'Successful transaction',
        '101' => 'Missing required fields',
        '102' => 'Invalid data',
        '110' => 'Partial amount approved',
        '150' => 'General system failure',
        '151' => 'The request was received but there was a server timeout. This error does not
        include timeouts between the client and the server',
        '152' => 'The request was received, but a service did not finish running in time',
        '200' => 'The authorization request was approved by the issuing bank but declined by
        CyberSource because it did not pass the AVS check',
        '201' => 'The issuing bank has questions about the request. You will not receive an
        authorization code programmatically, but you can obtain one verbally by calling
        the processor',
        '202' => 'Expired card',
        '203' => 'General decline of the card. No other information provided by the issuing bank',
        '204' => 'Insufficient funds in the account',
        '205' => 'Stolen or lost card',
        '207' => 'Issuing bank unavailable',
        '208' => 'Inactive card or card not authorized for card-not-present transactions',
        '209' => 'American Express Card Identification Digits (CIDs) did not match',
        '210' => 'The card has reached the credit limit',
        '211' => 'Invalid card verification number',
        '220' => "The processor declined the request based on a general issue with the
        customer's account",
        '221' => "The customer matched an entry on the processor's negative file",
        '222' => "The customer's bank account is frozen",
        '230' => 'The authorization request was approved by the issuing bank but declined by
        CyberSource because it did not pass the CVN check',
        '231' => 'Invalid account number',
        '232' => 'The card type is not accepted by the payment processor',
        '233' => 'General decline by the processor',
        '234' => 'There is a problem with your CyberSource merchant configuration',
        '236' => 'Processor failure',
        '240' => 'The card type sent is invalid or does not correlate with the card number',
        '250' => 'The request was received, but there was a timeout at the payment processor',
    );

    protected $_avsResponses = array(
            'A' => 'Street address matches, but 5-digit and 9-digit postal code do not match.',
            'B' => 'Street address matches, but postal code not verified.',
            'C' => 'Street address and postal code do not match.',
            'D' => 'Street address and postal code match.',
            'M' => 'Street address and postal code match.',
            'E' => 'AVS data is invalid or AVS is not allowed for this card type.',
            'F' => 'Card members name does not match, but billing postal code matches.',
            'F' => 'Card members name does not match, but billing postal code matches.',
            'G' => 'Issuing bank does not support AVS.',
            'H' => 'Card members name does not match. Street address and postal code match.',
            'I' => 'Address not verified.',
            'K' => 'Card members name and billing postal code match, but billing address does not match.',
            'M' => 'Street address and postal code do not match.',
            'N' => 'Card members name, street address and postal code do not match.',
            'P' => 'Postal code matches, but street address not verified.',
            'R' => 'System unavailable.',
            'S' => 'Issuing bank does not support AVS.',
            'T' => 'Card members name does not match, but street address matches.',
            'U' => 'Address information unavailable.',
            'W' => 'Street address does not match, but 9-digit postal code matches.',
            'X' => 'Street address and 9-digit postal code match.',
            'Y' => 'Street address and 5-digit postal code match.',
            'Z' => 'Street address does not match, but 5-digit postal code matches.',
            '1' => 'AVS is not supported for this processor or card type.',
            '2' => 'The processor returned an unrecognized value for the AVS response.',
        );

    protected $_cvnResponses = array(
            'D' => 'Transaction determined suspicious by issuing bank.',
            'I' => 'Card verification number failed processors data validation check.',
            'M' => 'Card verification number matched.',
            'N' => 'Card verification number not matched.',
            'P' => 'Card verification number not processed by processor for unspecified reason.',
            'S' => 'Card verification number is on the card but was not included in the request.',
            'U' => 'Card verification is not supported by the issuing bank.',
            'X' => 'Card verification is not supported by the card association.',
            '1' => 'Card verification is not supported for this processor or card type.',
            '2' => 'Unrecognized result code returned by processor for card verification response.',
            '3' => 'No result code returned by processor.',
        );
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Model\Region $region,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Framework\Stdlib\DateTime $dateFormat,
         PaymentConfig $paymentConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magedelight\Cybersource\Model\Config $cybersourceConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Session\Quote $sessionquote
    ) {
        $this->_addressConfig = $addressConfig;
        $this->_region = $region;
        $this->paymentConfig = $paymentConfig;
        $this->cybersourceConfig = $cybersourceConfig;
        $this->dateFormat = $dateFormat;
        $this->dateTime = $dateTime;
        $this->_storeManager = $storeManager;
        $this->sessionquote = $sessionquote;
        parent::__construct($context);
    }

    public function isEnabled()
    {
        return $this->getConfig('payment/magedelight_cybersource/active');
    }


    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getFormatedAddress($card)
    {
        $address = new \Magento\Framework\DataObject();
        $regionId = $card['region_id'];

        $regionName = ($regionId) ? $this->_region->load($regionId)->getName() : $card['state'];
        $address->addData(array(
            'firstname' => (string) $card['firstname'],
            'lastname' => (string) $card['lastname'],
            'company' => (string) $card['company'],
            'street1' => (string) $card['street'],
            'city' => (string) $card['city'],
            'region' => (string) $regionName,
            'postcode' => (string) $card['postcode'],
            'telephone' => (string) $card['telephone'],
            'country' => (string) $card['country_id'],
        ));

        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();

        return $renderer->renderArray($address->getData());
    }
    public function getErrorDescription($code)
    {
        if (!empty($code)) {
            return $this->_errorMessage[$code];
        }

        return;
    }
    public function getCcAvailableCardTypes($country = null)
    {
        $types = array_flip(explode(',', $this->cybersourceConfig->getCcTypes()));
        $mergedArray = [];

        if (is_array($types)) {
            foreach (array_keys($types) as $type) {
                $types[$type] = $this->getCcTypeNameByCode($type);
            }
        }

        //preserve the same credit card order
        $allTypes = $this->getCcTypes();
        if (is_array($allTypes)) {
            foreach ($allTypes as $ccTypeCode => $ccTypeName) {
                if (array_key_exists($ccTypeCode, $types)) {
                    $mergedArray[$ccTypeCode] = $ccTypeName;
                }
            }
        }

        return $mergedArray;
    }
    public function getCcTypes()
    {
        $ccTypes = $this->paymentConfig->getCcTypes();
        if (is_array($ccTypes)) {
            return $ccTypes;
        } else {
            return false;
        }
    }
    public function getCcTypeNameByCode($code)
    {
        $ccTypes = $this->paymentConfig->getCcTypes();
        if (isset($ccTypes[$code])) {
            return $ccTypes[$code];
        } else {
            return false;
        }
    }
    public function getTodayYear()
    {
        if (!$this->today) {
            $this->today = $this->dateTime->gmtTimestamp();
        }

        return date('Y', $this->today);
    }

    public function getTodayMonth()
    {
        if (!$this->today) {
            $this->today = $this->dateTime->gmtTimestamp();
        }

        return date('m', $this->today);
    }
    public function getTransactionMessage($payment, $requestType, $lastTransactionId, $card, $amount = false,
            $exception = false
        ) {
        return $this->getExtendedTransactionMessage(
                $payment, $requestType, $lastTransactionId, $card, $amount, $exception
            );
    }

    public function getExtendedTransactionMessage($payment, $requestType, $lastTransactionId, $card, $amount = false,
            $exception = false, $additionalMessage = false
        ) {
        $operation = $this->_getOperation($requestType);

        if (!$operation) {
            return false;
        }

        if ($amount) {
            $amount = sprintf('amount %s', $this->_formatPrice($payment, $amount));
        }

        if ($exception) {
            $result = sprintf('failed');
        } else {
            $result = sprintf('successful');
        }

        $card = sprintf('Credit Card: xxxx-%s', $card->getCcLast4());

        $pattern = '%s %s %s - %s.';
        $texts = array($card, $amount, $operation, $result);

        if (!is_null($lastTransactionId)) {
            $pattern .= ' %s.';
            $texts[] = sprintf('Cybersource Transaction ID %s', $lastTransactionId);
        }

        if ($additionalMessage) {
            $pattern .= ' %s.';
            $texts[] = $additionalMessage;
        }
        $pattern .= ' %s';
        $texts[] = $exception;

        return call_user_func_array('sprintf', array_merge(array($pattern), $texts));
    }
    protected function _getOperation($requestType)
    {
        switch ($requestType) {
                case self::REQUEST_TYPE_AUTH_ONLY:
                    return __('authorize');
                case self::REQUEST_TYPE_AUTH_CAPTURE:
                    return __('authorize and capture');
                case self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
                    return __('capture');
                case self::REQUEST_TYPE_CREDIT:
                    return __('refund');
                case self::REQUEST_TYPE_VOID:
                    return __('void');
                default:
                    return false;
            }
    }
    protected function _formatPrice($payment, $amount)
    {
        return $payment->getOrder()->getBaseCurrency()->formatTxt($amount);
    }
    public function getAvsLabel($avs)
    {
        if (isset($this->_avsResponses[ $avs ])) {
            return __(sprintf('%s (%s)', $avs, $this->_avsResponses[ $avs ]));
        }

        return $avs;
    }

    public function getCvnLabel($cvn)
    {
        if (isset($this->_cvnResponses[ $cvn ])) {
            return __(sprintf('%s (%s)', $cvn, $this->_cvnResponses[ $cvn ]));
        }

        return $cvn;
    }
    public function checkAdmin()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $app_state = $om->get('Magento\Framework\App\State');
        $area_code = $app_state->getAreaCode();
        if ($app_state->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return true;
        } else {
            return false;
        }
    }

    public function getWebsiteId(){
        if($this->checkAdmin()){
            $storeId =  $this->sessionquote->getQuote()->getStoreId();
            $store = $this->_storeManager->getStore($storeId);
            $website_id = $store->getWebsiteId();
        } else {
            $website_id =  $this->_storeManager->getStore()->getWebsiteId();
        }
        return $website_id;
    }

    public function getCustomerAccountScope()
    {
        return $this->cybersourceConfig->getCustomerAccountShare();
    }
}
