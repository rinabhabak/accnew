<?php
/**
 * Alpine_DisableNewsletter
 *
 * @category    Alpine
 * @package     Alpine_DisableNewsletter
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Aleksandr Mikhailov (aleksandr.mikhailov@alpineinc.com)
 */

namespace Alpine\DisableNewsletter\Controller\Newsletter\Manage;

use Alpine\DisableNewsletter\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Index
 *
 * @category    Alpine
 * @package     Alpine_DisableNewsletter
 */
class Index extends \Magento\Newsletter\Controller\Manage\Index
{
    /**
     * Alpine_DisableNewsletter Helper
     *
     * @var Data
     */
    protected $helper;

    /**
     * Index constructor
     *
     * @param Context $context
     * @param Session $customerSession
     * @param Data    $helper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context, $customerSession);
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if ($this->helper->isNewsletterEnabled()) {
            parent::execute();
        } else {
            return $this->_redirect('customer/account');
        }
    }
}
