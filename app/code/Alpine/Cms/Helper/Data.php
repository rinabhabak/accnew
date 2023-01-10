<?php
/**
 * Quote Form Helper
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Cms\Helper;

use Alpine\Cms\Model\Quote\Config;
use Magento\Contact\Helper\Data as ContactData;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Information;

/**
 * Quote Form Helper
 *
 * @category    Alpine
 * @package     Alpine_Cms
 */
class Data extends ContactData
{
    /**
     * Enabled config path
     *
     * @var string
     */
    const XML_PATH_ENABLED = Config::XML_PATH_ENABLED;

    /**
     * After Tier Content config path
     *
     * @var string
     */
    const AFTER_TIER_CONTENT = 'alpine_cms_quote/after_tier_content/editor_textarea';

    /**
     * Data persistor
     *
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * Post data
     *
     * @var array
     */
    private $postData = null;

    /**
     * Customer interface
     *
     * @var CustomerInterface $customer
     */
    private $customer;

    /**
     * Data constructor
     *
     * @param Context $context
     * @param Session $customerSession
     * @param CustomerViewHelper $customerViewHelper
     */
    public function __construct(Context $context, Session $customerSession, CustomerViewHelper $customerViewHelper)
    {
        parent::__construct($context, $customerSession, $customerViewHelper);
        $this->customer = $customerSession->getCustomerDataObject();
    }

    /**
     * Get value from POST by key
     *
     * @param string $key
     * @return string
     */
    public function getPostValue($key)
    {
        if (null === $this->postData) {
            $this->postData = (array)$this->getDataPersistor()->get('alpine_quote_form');
            $this->getDataPersistor()->clear('alpine_quote_form');
        }

        if (isset($this->postData[$key])) {
            return (string)$this->postData[$key];
        }

        return '';
    }

    /**
     * Get Data Persistor
     *
     * @return DataPersistorInterface
     */
    private function getDataPersistor()
    {
        if ($this->dataPersistor === null) {
            $this->dataPersistor = ObjectManager::getInstance()
                ->get(DataPersistorInterface::class);
        }

        return $this->dataPersistor;
    }

    /**
     * Get user first name
     *
     * @return string
     */
    public function getFirstName()
    {
        if (!$this->_customerSession->isLoggedIn() || !$this->customer) {
            return '';
        }
        return trim($this->customer->getFirstname());
    }

    /**
     * Get user last name
     *
     * @return string
     */
    public function getLastName()
    {
        if (!$this->_customerSession->isLoggedIn() || !$this->customer) {
            return '';
        }
        return trim($this->customer->getLastname());
    }

    /**
     * get General Store Information Phone
     *
     * @return string
     */
    public function getGeneralStoreInformationPhone()
    {
        return $this->scopeConfig->getValue(Information::XML_PATH_STORE_INFO_PHONE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get After Tier Price Content
     *
     * @return string
     */
    public function getAfterTierPriceContent()
    {
        return $this->scopeConfig->getValue(SELF::AFTER_TIER_CONTENT, ScopeInterface::SCOPE_STORE);
    }
}