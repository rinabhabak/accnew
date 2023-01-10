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

class Add extends \Magento\Backend\App\Action
{
    protected $resultLayoutFactory;
    public function __construct(
            \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();

        return $resultLayout;
    }

    protected function _isAllowed()
    {
        return true;
    }
}
