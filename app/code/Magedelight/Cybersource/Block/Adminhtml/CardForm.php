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
namespace Magedelight\Cybersource\Block\Adminhtml;

class CardForm extends \Magento\Directory\Block\Data
{
    protected $_template = 'cards/form.phtml';
    protected $_address = null;

    protected $_customerFactory = null;
    protected $_addressRepository;

    protected $addressDataFactory;

    protected $currentCustomer;

    protected $dataObjectHelper;

    protected $cybersourceConfig;

    protected $paymentConfig;
    protected $_card;
    protected $jsonHelper;

    public function __construct(
             \Magento\Framework\View\Element\Template\Context $context,
             \Magento\Directory\Helper\Data $directoryHelper,
             \Magento\Framework\Json\EncoderInterface $jsonEncoder,
             \Magento\Customer\Model\CustomerFactory $customerFactory,
             \Magento\Framework\App\Cache\Type\Config $configCacheType,
             \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
             \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
             \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
             \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
             \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
             \Magedelight\Cybersource\Model\Config $cybersourceConfig,
             \Magento\Payment\Model\Config $paymentConfig,
             \Magento\Framework\Json\Helper\Data $jsonHelper,
             array $data = []
    ) {
        $this->_addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->cybersourceConfig = $cybersourceConfig;
        $this->_customerFactory = $customerFactory;
        $this->paymentConfig = $paymentConfig;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $directoryHelper, $jsonEncoder, $configCacheType, $regionCollectionFactory, $countryCollectionFactory, $data);
    }

    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();
        $availableTypes = explode(',', $this->cybersourceConfig->getCcTypes());
        if ($availableTypes) {
            foreach ($types as $code => $name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }

        return $types;
    }
    public function getCustomer()
    {
        $id = $this->getRequest()->getParam('id');

        return $this->_customerFactory->create()->load($id);
    }
    protected function _getConfig()
    {
        return $this->paymentConfig;
    }

    public function getCcMonths()
    {
        return $this->_getConfig()->getMonths();
    }

    public function getCcYears()
    {
        return $this->_getConfig()->getYears();
    }

    public function hasVerification()
    {
        return $this->cybersourceConfig->isCardVerificationEnabled();
    }

    public function setCard($card)
    {
        $this->_card = $card;

        return $this;
    }

    public function getCard()
    {
        if (empty($this->_card)) {
            return;
        }

        return $this->jsonHelper->jsonDecode($this->_card);
    }

    public function getRegionValue($regionValue, $countryId)
    {
        $regionId = null;
        $regionCollection = $this->_regionCollectionFactory->create()
                ->addFieldToFilter('default_name', ['eq' => $regionValue])
                ->addFieldToFilter('country_id', ['eq' => $countryId]);
        if ($regionCollection->count() > 0) {
            $regionId = $regionCollection->getFirstItem()->getId();
        } else {
            $regionId = $regionValue;
        }

        return $regionId;
    }
}
