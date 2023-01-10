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

class Edit extends \Magento\Directory\Block\Data
{
    protected $_cardModel;

    protected $urlBuilder;

    protected $_countryCollectionFactory;

    protected $_storeManager;

    protected $_configCacheType;

    protected $scopeConfig;

    protected $paymentConfig;

    protected $directoryHelper;

    protected $getconfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magedelight\Cybersource\Model\Cards $cardModel,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magedelight\Cybersource\Model\Config $getconfig,
        array $data = []
    ) {
        $this->_cardModel = $cardModel;
        $this->_jsonEncoder = $jsonEncoder;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->_storeManager = $context->getStoreManager();
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_configCacheType = $configCacheType;
        $this->scopeConfig = $context->getScopeConfig();
        $this->paymentConfig = $paymentConfig;
        $this->directoryHelper = $directoryHelper;
        $this->getconfig = $getconfig;

        parent::__construct($context,$directoryHelper,$jsonEncoder,$configCacheType,$regionCollectionFactory,$countryCollectionFactory, $data);
    }

    public function getCard()
    {
        $cardId = $this->getRequest()->getPostValue('card_id');
        if (!empty($cardId)) {
            $cardData = $this->_cardModel->load($cardId);

            return $cardData->getData();
        } else {
            return;
        }
    }
    public function getBackUrl()
    {
        return $this->urlBuilder->getUrl('magedelight_cybersource/cards/listing');
    }

    public function getSaveUrl()
    {
        return $this->urlBuilder->getUrl('magedelight_cybersource/cards/update');
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
    public function hasVerification()
    {
        return $this->getconfig->isCardVerificationEnabled();
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
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
