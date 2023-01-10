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

class Delete extends \Magento\Framework\View\Element\Template
{
    protected $allcardcollection;

    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Cybersource\Model\Cards $allcardcollection,
        array $data = []
    ) {
        $this->_allcardcollection = $allcardcollection;
        $this->urlBuilder = $context->getUrlBuilder();
        parent::__construct($context, $data);
    }

    public function getCustomer()
    {
        return $this->_customer;
    }

    public function getBackUrl()
    {
        return $this->urlBuilder->getUrl('magedelight_cybersource/cards/list');
    }

    public function getSaveUrl()
    {
        return $this->urlBuilder->getUrl('magedelight_cybersource/cards/save');
    }
}
