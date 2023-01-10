<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Rolepermissions
 */


namespace Amasty\Rolepermissions\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;
use Amasty\Rolepermissions\Block\Adminhtml\Role\Tab\Scope;

class BlockCreateAfterObserver implements ObserverInterface
{
    /**
     * @var \Amasty\Rolepermissions\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    private $backendHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    public function __construct(
        \Amasty\Rolepermissions\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Backend\Helper\Data $backendHelper
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->response = $response;
        $this->backendHelper = $backendHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        $rule = $this->helper->currentRule();

        if ($block instanceof \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit
            && $rule->getScopeAccessMode() != Scope::MODE_NONE
        ) {
            $block->removeButton('save')->removeButton('save_and_edit_button')->removeButton('delete');
            return;
        }

        if (!($block instanceof \Magento\Backend\Block\Store\Switcher)) {
            return;
        }

        if (!$this->request->getParam('store') && !$this->request->getParam('website')) {
            $views = $rule->getScopeStoreviews();
            if ($views) { // Redirect to first available store view
                $redirectUrl = $this->backendHelper->getUrl(
                    '*/*/*',
                    [
                        '_current' => true,
                        'store'    => $views[0]
                    ]
                );

                $this->response->setRedirect($redirectUrl);
            }
        }
    }
}
