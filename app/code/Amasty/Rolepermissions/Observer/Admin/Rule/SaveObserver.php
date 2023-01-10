<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Rolepermissions
 */


namespace Amasty\Rolepermissions\Observer\Admin\Rule;

use Amasty\Rolepermissions\Block\Adminhtml\Role\Tab\Attributes;
use Amasty\Rolepermissions\Block\Adminhtml\Role\Tab\Categories;
use Amasty\Rolepermissions\Block\Adminhtml\Role\Tab\Products;
use Amasty\Rolepermissions\Model\RuleFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class SaveObserver implements ObserverInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        RuleFactory $ruleFactory,
        Registry $registry,
        StoreManagerInterface $storeManager
    ) {
        $this->coreRegistry = $registry;
        $this->ruleFactory = $ruleFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $role = $this->coreRegistry->registry('current_role');

        if (!$role->getId()) {
            return;
        }
        $request = $observer->getRequest();
        $data = $request->getParam('amrolepermissions');

        if (!$data) {
            return;
        }
        /** @var  \Amasty\Rolepermissions\Model\Rule $rule */
        $rule = $this->ruleFactory->create();
        $rule = $rule->load($role->getId(), 'role_id');

        $rule->setScopeWebsites([])
            ->setScopeStoreviews([]);

        $data['role_id'] = $role->getId();

        if (isset($data['product_access_mode'])) {
            switch ($data['product_access_mode']) {
                case Products::MODE_ANY:
                case Products::MODE_MY:
                case Products::MODE_SCOPE:
                    $data['products'] = [];
                    break;
                case Products::MODE_SELECTED:
                    $data['products'] = explode('&', $data['products']);
                    break;
            }
        }

        if (isset($data['attribute_access_mode'])) {
            switch ($data['attribute_access_mode']) {
                case Attributes::MODE_ANY:
                    $data['attributes'] = [];
                    break;
                case Attributes::MODE_SELECTED:
                    $data['attributes'] = explode('&', $data['attributes']);
                    break;
            }
        }

        if (isset($data['category_access_mode'])) {
            switch ($data['category_access_mode']) {
                case Categories::MODE_ALL:
                    $data['categories'] = [];
                    break;
                case Categories::MODE_SELECTED:
                    $data['categories'] = $this->getFormattedCategories($data);
                    break;
            }
        }

        if (isset($data['role_access_mode'])) {
            switch ($data['role_access_mode']) {
                case Attributes::MODE_ANY:
                    $data['roles'] = [];
                    break;
                case Attributes::MODE_SELECTED:
                    $data['roles'] = explode('&', $data['roles']);
                    break;
            }
        }
        $rule->addData($data);

        $rule->save();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getFormattedCategories($data)
    {
        $categories = explode(',', str_replace(' ', '', $data['categories']));
        $rootCategories = $this->getRootCategories($data);
        $categories = array_values(
            array_unique(
                array_merge(
                    $rootCategories,
                    $categories
                )
            )
        );

        return array_filter($categories);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getRootCategories($data)
    {
        $rootCategories = [];
        $allStores = $this->storeManager->getStores();

        switch (true) {
            case isset($data['scope_storeviews']):
                foreach ($data['scope_storeviews'] as $storeId) {
                    array_push($rootCategories, $allStores[$storeId]->getRootCategoryId());
                }
                break;
            case isset($data['scope_websites']):
                foreach ($allStores as $store) {
                    if (in_array($store->getWebsiteId(), $data['scope_websites'])) {
                        array_push($rootCategories, $store->getRootCategoryId());
                    }
                }
                break;
            default:
                foreach ($allStores as $store) {
                    array_push($rootCategories, $store->getRootCategoryId());
                }
        }
        $rootCategories = array_unique($rootCategories);

        return $rootCategories;
    }
}
