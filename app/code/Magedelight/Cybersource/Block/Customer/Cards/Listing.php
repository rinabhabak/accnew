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

class Listing extends \Magento\Framework\View\Element\Template
{
    protected $allcardcollection;

    protected $_storeManager;

    protected $_customer = null;

    protected $_countryModel = null;

    protected $urlBuilder;

    protected $addressRender;

    protected $filterManager;

    protected $dataObject;

    protected $magedelightConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Cybersource\Model\Cards $allcardcollection,
        \Magento\Customer\Model\Session $customer,
        \Magento\Customer\Block\Address\Renderer\DefaultRenderer $addressRender,
        \Magento\Framework\DataObject $dataObject,
        \Magedelight\Cybersource\Model\Config $magedelightConfig,
        \Magedelight\Cybersource\Helper\Data $cybersourceHelper,    
        \Magento\Directory\Model\Country $countryModel,
        array $data = []
    ) {
        $this->_allcardcollection = $allcardcollection;
        $this->_storeManager = $context->getStoreManager();
        $this->_customer = $customer;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->addressRender = $addressRender;
        $this->filterManager = $context->getFilterManager();
        $this->dataObject = $dataObject;
        $this->magedelightConfig = $magedelightConfig;
        $this->cybersourceHelper    = $cybersourceHelper;
        $this->_countryModel = $countryModel;
        parent::__construct($context, $data);
    }

    public function getCustomer()
    {
        return $this->_customer;
    }

    public function getCustomerCards()
    {
        $customerId = $this->_customer->getId();
        $websiteId = $this->cybersourceHelper->getWebsiteId();
        $customer_account_scope =  $this->cybersourceHelper->getCustomerAccountScope();
        $result = array();
        if (!empty($customerId)) {
            $result = $this->_allcardcollection->getCollection()
                            ->addFieldToFilter('customer_id', $customerId);
            if ($customer_account_scope) {
               $result->addFieldToFilter('website_id', $websiteId);
            }
            $result->getData();
        }
        return $result;
    }

    public function getAddressHtml($_card)
    {
        $typeObject = $this->dataObject;
        $typeObject->addData(array(
            'code' => 'html',
            'title' => 'HTML',
            'default_format' => $this->magedelightConfig->getDefaultFormat(),
        ));
        $this->addressRender->setType($typeObject);
        $data = array();
        $countryName = '';
        if (!empty($_card['country_id'])) {
            $countryName = $this->_countryModel->loadByCode($_card['country_id'])->getName();
        }
        $data['firstname'] = $_card['firstname'];
        $data['lastname'] = $_card['lastname'];
        $data['street'] = $_card['street'];
        $data['city'] = $_card['city'];
        $data['country_id'] = $_card['country_id'];
        $data['country'] = $countryName;
        $data['region_id'] = $_card['region_id'];
        $data['postcode'] = $_card['postcode'];
        $data['telephone'] = $_card['telephone'];
        $format = $this->addressRender->getFormatArray($data);

        return $this->filterManager->template($format, ['variables' => $data]);
    }
    public function getBackUrl()
    {
        return $this->urlBuilder->getUrl('customer/account');
    }

    public function getAddCardUrl()
    {
        return $this->urlBuilder->getUrl('magedelight_cybersource/cards/add');
    }

    public function getPostUrl()
    {
        return $this->urlBuilder->getUrl('magedelight_cybersource/cards/edit');
    }

    public function getDeleteAction()
    {
        return $this->urlBuilder->getUrl('magedelight_cybersource/cards/delete');
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
}
