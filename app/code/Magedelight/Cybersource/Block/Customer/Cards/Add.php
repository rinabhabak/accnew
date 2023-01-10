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
namespace Magedelight\Cybersource\Block\Customer\Cards;

class Add extends \Magento\Directory\Block\Data
{
    protected $allcardcollection;

    protected $_storeManager;

    protected $_configCacheType;

    protected $scopeConfig;

    protected $_customer = null;

    protected $urlBuilder;

    protected $paymentConfig;

    protected $getconfig;

    protected $directoryHelper;

    protected $_countryCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
         \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magedelight\Cybersource\Model\Cards $allcardcollection,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customer,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magedelight\Cybersource\Model\Config $getconfig,
        array $data = []
    ) {
        $this->_allcardcollection = $allcardcollection;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->_configCacheType = $configCacheType;
        $this->_customer = $customer->getCustomer();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->directoryHelper = $directoryHelper;
        $this->getconfig = $getconfig;
        $this->paymentConfig = $paymentConfig;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_countryCollectionFactory = $countryCollectionFactory;
        parent::__construct($context,$directoryHelper,$jsonEncoder,$configCacheType,$regionCollectionFactory,$countryCollectionFactory, $data);
    }

    public function getCustomer()
    {
        return $this->_customer;
    }

    public function getBackUrl()
    {
        return $this->urlBuilder->getUrl('magedelight_cybersource/cards/listing');
    }

    public function getSaveUrl()
    {
        return $this->urlBuilder->getUrl('magedelight_cybersource/cards/save');
    }

    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getCcAvailableTypes()
    {
        $types = $this->paymentConfig->getCcTypes();
        $availableTypes = explode(',', $this->getconfig->getCcTypes());
        if ($availableTypes) {
            foreach ($types as $code => $name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }

        return $types;
    }

    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] = __('Month');
            $months = array_merge($months, $this->paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }

        return $months;
    }

    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (!($years)) {
            $years = $this->paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    public function hasVerification()
    {
        return $this->getconfig->isCardVerificationEnabled();
    }

    public function getPostUrl()
    {
        return $this->urlBuilder->getUrl('magedelight_cybersource/cards/edit');
    }
}
