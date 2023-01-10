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
namespace Magedelight\Cybersource\Controller\Adminhtml\Cards;

class Edit extends \Magento\Backend\App\Action
{
    protected $_cybersourceConfig;
    protected $_cybersourceHelper;
    protected $_customerFactory;
    protected $_cardmodelFactory;
    protected $_jsonEncoder;
    protected $resultLayoutFactory;
    public function __construct(
            \Magento\Backend\App\Action\Context $context,
            \Magedelight\Cybersource\Model\Config $cybersourceConfig,
            \Magedelight\Cybersource\Helper\Data $cybersourceHelper,
             \Magedelight\Cybersource\Model\CardsFactory $cardmodelFactory,
            \Magento\Customer\Model\CustomerFactory $customerFactory,
            \Magento\Framework\Json\Encoder $jsonEncoder,
            \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->_cybersourceConfig = $cybersourceConfig;
        $this->_cybersourceHelper = $cybersourceHelper;
        $this->_customerFactory = $customerFactory;
        $this->_cardmodelFactory = $cardmodelFactory;
        $this->_jsonEncoder = $jsonEncoder;
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $customerId = $this->getRequest()->getParam('id', null);
        $customer = $this->_customerFactory->create()->load($customerId);
        $editId = $this->getRequest()->getParam('customercardid', null);
        if (!is_null($editId)) {
            $cardmodel = $this->_cardmodelFactory->create();
            $cardModel = $cardmodel->load($editId);
            if ($cardModel->getId()) {
                $card = $cardModel->getData();
            }
        }
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('magedelight.cybersource.ajax.form')->setCard($this->_jsonEncoder->encode($card));

        return $resultLayout;
    }
    protected function _isAllowed()
    {
        return true;
    }
}
