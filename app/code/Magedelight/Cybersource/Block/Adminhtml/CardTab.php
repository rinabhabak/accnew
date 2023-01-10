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

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class CardTab extends \Magento\Backend\Block\Template implements TabInterface
{
    protected $_coreRegistry;
    protected $allcardcollection;
    protected $_cards;
    protected $_template = 'tab/savedCards.phtml';
    protected $_cybersourceConfig;
    protected $xmlApi;
    protected $soapApi;
    protected $cybersourceHelper;
    protected $customerFactory;
    protected $jsonEncoder;
    protected $customerid;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Cybersource\Model\Cards $allcardcollection,
        \Magedelight\Cybersource\Helper\Data $cybersourceHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->_allcardcollection = $allcardcollection;
        $this->_coreRegistry = $registry;
        $this->cybersourceHelper = $cybersourceHelper;
        $this->customerFactory = $customerFactory;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }
    public function getCustomerId()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        if (empty($customerId)) {
            $customerId = $this->customerid;
        }

        return $customerId;
    }
    public function getTabLabel()
    {
        return __('Saved Cybersource Cards');
    }
    public function getTabTitle()
    {
        return __('Saved Cybersource Cards');
    }
    public function setCustomerId($customerid)
    {
        $this->customerid = $customerid;
    }
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }

        return false;
    }
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }

        return true;
    }
    public function getTabClass()
    {
        return '';
    }
    public function getTabUrl()
    {
        return '';
    }
    public function isAjaxLoaded()
    {
        return false;
    }
    public function getCards()
    {
        $customerId = $this->getCustomerId();

        $result = array();
        if (!empty($customerId)) {
            $result = $this->_allcardcollection->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->getData();
        }

        return $result;
    }

    public function getFormatedAddress($card)
    {
        return $this->cybersourceHelper->getFormatedAddress($card);
    }

    public function getCardsInJson()
    {
        return $this->jsonEncoder->encode($this->getCards());
    }

    public function getDeleteActionUrl()
    {
        return $this->getUrl('magedelight_cybersource/cards/delete', ['id' => $this->getCustomerId()]);
    }

    public function getEditCardAction()
    {
        return $this->getUrl('magedelight_cybersource/cards/edit', ['id' => $this->getCustomerId()]);
    }

    public function getAddAction()
    {
        return $this->getUrl('magedelight_cybersource/cards/add', ['id' => $this->getCustomerId()]);
    }
}
