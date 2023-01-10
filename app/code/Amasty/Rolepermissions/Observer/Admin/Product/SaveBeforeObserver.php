<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Rolepermissions
 */


namespace Amasty\Rolepermissions\Observer\Admin\Product;

use Amasty\Rolepermissions\Block\Adminhtml\Role\Tab\Scope;
use Magento\Framework\Event\ObserverInterface;

class SaveBeforeObserver implements ObserverInterface
{
    /**
     * @var \Amasty\Rolepermissions\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Amasty\Rolepermissions\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->authorization = $authorization;
        $this->authSession = $authSession;
        $this->productResource = $productResource;
        $this->productRepository = $productRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->request->getModuleName() == 'api') {
            return;
        }

        $websiteIds = [];
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getProduct();

        if (!$this->authorization->isAllowed('Amasty_Rolepermissions::save_products')) {
            $this->helper->redirectHome();
        }

        if (!$this->authorization->isAllowed('Amasty_Rolepermissions::product_owner')
            && $this->authSession->getUser()) {
            $product->unsetData('amrolepermissions_owner');
        }

        $rule = $this->helper->currentRule();

        if (!$rule) {
            return;
        }

        if (!$rule->checkProductPermissions($product)
            && !$rule->checkProductOwner($product)
        ) {
            $this->helper->redirectHome();
        }

        if ($rule->getScopeStoreviews()) {
            if (!$product->getId()) {
                $product->setData('amrolepermissions_disable');
            }
        }
    }
}
