<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Rolepermissions
 */


namespace Amasty\Rolepermissions\Observer\Admin;

use Amasty\Rolepermissions\Helper\Data;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as EavCollection;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;

class CollectionLoadBeforeObserver implements ObserverInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var EavCollection
     */
    private $entity;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var Website
     */
    private $website;

    public function __construct(
        Registry $registry,
        EavCollection $entity,
        Data $helper,
        ModuleManager $moduleManager,
        Website $website,
        Store $store
    ) {
        $this->registry = $registry;
        $this->entity = $entity;
        $this->helper = $helper;
        $this->moduleManager = $moduleManager;
        $this->website = $website;
        $this->store = $store;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Select_Exception
     */
    public function execute(Observer $observer)
    {
        /** @var \Amasty\Rolepermissions\Model\Rule $rule */
        $rule = $this->registry->registry('current_amrolepermissions_rule');

        if (!$rule) {
            return;
        }

        $collection = $observer->getCollection();

        if ($rule->getScopeStoreviews()) {
            if ($this->isCollectionHasStoreFilterWithParam($collection)) {
                $collection->addStoreFilter($rule->getScopeStoreviews(), false);
            }

            if ($this->isCollectionHasStoreFilter($collection)) {
                $collection->addStoreFilter($rule->getScopeStoreviews());
            }

            if ($this->isCollectionHasStoreIdField($collection)) {
                $this->addStoreFilter($collection, $rule);
            }

            if ($this->isCollectionHasSetStoreIdsMethod($collection)) {
                $collection->setStoreIds($rule->getScopeStoreviews());
            }

            if ($this->isResourceModelHasStoreIdField($collection)) {
                $this->addStoreFilter($collection, $rule);
            }

            if ($collection instanceof \Magento\Review\Model\ResourceModel\Review\Product\Collection) {
                $this->addStoreFilterToReview($collection, $rule);
            }

            if ($collection instanceof \Magento\Customer\Model\ResourceModel\Grid\Collection) {
                $this->addCustomerFilter($collection, $rule);
            }

            if ($collection instanceof \Amasty\Groupcat\Model\ResourceModel\Rule\Collection) {
                $this->addStoreFilterToGroupcatRules($collection, $rule);
            }

            if ($collection instanceof \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Grid\Collection
                || $collection instanceof \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Collection
            ) {
                $this->addFilterToWarehouseCollection($collection, $rule);
            }
        }

        $this->addCategoryFilter($collection, $rule);
        $this->addAttributesFilter($collection, $rule);
    }

    /**
     * @param $collection
     * @param $rule
     *
     * @throws \Zend_Db_Select_Exception
     */
    public function addStoreFilter($collection, $rule)
    {
        if ($collection instanceof \Magento\Store\Model\ResourceModel\Store\Collection
            || $collection instanceof \Magento\Store\Model\ResourceModel\Website\Collection
        ) {
            /** Sometimes we shouldn't change store collection to load default values from default stores */
            if ($this->registry->registry('am_dont_change_collection')) {
                $this->registry->unregister('am_dont_change_collection');

                return;
            }
        }

        if ($collection instanceof \Amasty\Shiprules\Model\ResourceModel\Rule\Collection) {
            $collection->addStoreFilter($rule->getScopeStoreviews());
        }

        /** Its unable to retrieve RequestQuote grid collection plain select, but it can be modified */
        if (!$collection->getSelect()->__toString()
            && !$collection instanceof \Amasty\RequestQuote\Model\ResourceModel\Quote\Grid\Collection
        ) {
            return;
        }

        $collectionClone = clone $collection;//for not loading of main collection before filters
        $collectionData = $collectionClone->getData();

        if (isset($collectionData[0])) {
            if (key_exists('store_id', $collectionData[0])) {
                $alias = '';

                if ($this->isCollectionSelectNeedAlias($collection)) {
                    $select = $collection->getSelect();
                    $alias = $this->getMainAlias($select) ? $this->getMainAlias($select) . '.' : '';
                }

                $allowedStoreViews = $rule->getScopeStoreviews();
                $collection->addFieldToFilter($alias . 'store_id', ['in' => $allowedStoreViews]);
            }
        }
    }

    /**
     * @param $collection
     * @param \Amasty\Rolepermissions\Model\Rule $rule
     */
    private function addCustomerFilter($collection, $rule)
    {
        if (!$collection->getSelect()->__toString()) {
            return;
        }

        if ($allowedWebsites = $rule->getScopeWebsites()) {
            $collection->addFieldToFilter('website_id', ['in' => $allowedWebsites]);
        } elseif ($allowedStoreViews = $rule->getScopeStoreviews()) {
            $collection->getSelect()->joinLeft(
                ['customer_data' => $collection->getTable('customer_entity')],
                'main_table.entity_id = customer_data.entity_id',
                []
            )->where('customer_data.store_id IN (' . implode(',', $allowedStoreViews) . ')');
        }
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    private function isCollectionHasStoreIdField($collection)
    {
        return $collection instanceof \Magento\Wishlist\Model\ResourceModel\Item\Collection
            || $collection instanceof \Magento\Tax\Model\ResourceModel\Calculation\Rate\Title\Collection
            || $collection instanceof \Magento\Search\Model\ResourceModel\SynonymGroup\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Item\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection
            || $collection instanceof \Magento\Quote\Model\ResourceModel\Quote\Item\Collection
            || $collection instanceof \Magento\Quote\Model\ResourceModel\Quote\Collection
            || $collection instanceof \Magento\Paypal\Model\ResourceModel\Report\Settlement\Row\Collection
            || $collection instanceof \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection
            || $collection instanceof \Magento\Widget\Model\ResourceModel\Layout\Link\Collection
            || $collection instanceof \Magento\Eav\Model\ResourceModel\Form\Type\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Invoice\Grid\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Shipment\Grid\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
            || $collection instanceof \Magento\Customer\Model\ResourceModel\Online\Grid\Collection
            || $collection instanceof \Magento\Store\Model\ResourceModel\Website\Collection
            || $collection instanceof \Magento\Customer\Model\ResourceModel\Customer\Collection
            || $collection instanceof \Amasty\Shiprules\Model\ResourceModel\Rule\Collection;
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    private function isCollectionHasStoreFilter($collection)
    {
        return $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Sale\Collection
            || $collection instanceof \Magento\Review\Model\ResourceModel\Review\Collection
            || $collection instanceof \Magento\Reports\Model\ResourceModel\Quote\Item\Collection
            || $collection instanceof \Magento\Newsletter\Model\ResourceModel\Queue\Collection
            || $collection instanceof \Magento\Widget\Model\ResourceModel\Layout\Update\Collection
            || $collection instanceof \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection
            || $collection instanceof \Magento\Catalog\Model\ResourceModel\Category\Flat\Collection
            || $collection instanceof \Magento\Cms\Model\ResourceModel\AbstractCollection
            || $collection instanceof \Magento\Reports\Model\ResourceModel\Event\Collection
            || $collection instanceof \Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection
            || $collection instanceof \Magento\Swatches\Model\ResourceModel\Swatch\Collection
            || $collection instanceof \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection
            || $collection instanceof \Magento\Search\Model\ResourceModel\Query\Collection
            || $collection instanceof \Magento\Sitemap\Model\ResourceModel\Sitemap\Collection
            || $collection instanceof \Magento\Review\Model\ResourceModel\Review\Summary\Collection
            || $collection instanceof \Magento\Theme\Model\ResourceModel\Design\Collection;
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    private function isCollectionSelectNeedAlias($collection)
    {
        return ($collection instanceof \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
            && $this->moduleManager->isEnabled('Amasty_Ogrid'));
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    private function isCollectionHasStoreFilterWithParam($collection)
    {
        return $collection instanceof \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection
            || $collection instanceof \Magento\Widget\Model\ResourceModel\Widget\Instance\Collection
            || $collection instanceof \Magento\Cms\Model\ResourceModel\Block\Grid\Collection
            || $collection instanceof \Magento\Cms\Model\ResourceModel\Page\Grid\Collection;
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    private function isCollectionHasSetStoreIdsMethod($collection)
    {
        return $collection instanceof \Magento\Reports\Model\ResourceModel\Accounts\Collection
            || $collection instanceof \Magento\Reports\Model\ResourceModel\Report\Collection;
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    private function isCollectionHasOnlyWebSiteField($collection)
    {
        return $collection instanceof \Magento\Customer\Model\ResourceModel\Grid\Collection;
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    private function isResourceModelHasStoreIdField($collection)
    {
        $resourceModel = $collection->getResource();

        return $resourceModel instanceof \Magento\Theme\Model\ResourceModel\Design\Config
            || $resourceModel instanceof \Magento\Search\Model\ResourceModel\SynonymGroup;
    }

    /**
     * @param $collection
     * @param $rule
     */
    private function addFilterToWarehouseCollection($collection, $rule)
    {
        if ($collection->getFlag('am_warehouse_clone')) {
            return;
        }

        $allowedWarehouseIds = [];
        $allowedStoreViews = $rule->getScopeStoreviews();
        $collectionClone = clone $collection;
        $collectionClone->setFlag('am_warehouse_clone', true);

        $collectionClone->getSelect()
            ->reset(Select::COLUMNS)
            ->reset(Select::WHERE)
            ->columns(['warehouse_id'])
            ->group('main_table.warehouse_id')
            ->joinLeft(
                ['warehouse_store_table' => $collectionClone->getTable('amasty_multiinventory_store')],
                'main_table.warehouse_id = warehouse_store_table.warehouse_id',
                []
            )->where('warehouse_store_table.store_id IN (?)', implode(',', $allowedStoreViews));

        foreach ($collectionClone as $item) {
            $allowedWarehouseIds[] = $item->getId();
        }

        // keep access to default warehouse
        if (!in_array(1, $allowedWarehouseIds)) {
            $allowedWarehouseIds[] = 1;
        }

        $select = $collection->getSelect();
        $alias = $this->getMainAlias($select) ? $this->getMainAlias($select) . '.' : '';
        $collection->addFieldToFilter($alias . 'warehouse_id', ['in' => $allowedWarehouseIds]);
    }

    /**
     * @param $collection
     * @param $rule
     *
     * @throws \Zend_Db_Select_Exception
     */
    private function addStoreFilterToGroupcatRules($collection, $rule)
    {
        $select = $collection->getSelect();
        $alias = $this->getMainAlias($select) ? $this->getMainAlias($select) . '.' : '';
        $allowedStoreViews = $rule->getScopeStoreviews();
        $where = $select->getPart(\Zend_Db_Select::WHERE);

        foreach ($where as $key => $condition) {
            if (strpos($condition, 'rule_id') !== false) {
                $where[$key] = str_replace('rule_id', $alias . 'rule_id', $condition);
            }
        }

        $collection->getSelect()->reset(\Zend_Db_Select::WHERE);
        $collection->getSelect()->joinLeft(
            ['rule_store' => $collection->getTable('amasty_groupcat_rule_store')],
            'main_table.rule_id = rule_store.rule_id',
            []
        )->where('rule_store.store_id IN (?)', implode(',', $allowedStoreViews));
    }

    /**
     * @param $collection
     * @param $rule
     */
    private function addStoreFilterToReview($collection, $rule)
    {
        $storesIds = $rule->getScopeStoreviews();

        foreach ($storesIds as $storesId) {
            $collection->addStoreFilter($storesId);
        }
    }

    /**
     * @param $collection
     * @param $rule
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addCategoryFilter($collection, $rule)
    {
        if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Category\Collection) {
            $ruleCategories = $rule->getCategories();

            if ($ruleCategories) {
                $ruleCategories = $this->helper->getParentCategoriesIds($ruleCategories);
                $collection->addFieldToFilter('entity_id', ['in' => $ruleCategories]);
            } else {
                $rootCategories = [];
                /** Hide categories from another store */
                if ($rule->getScopeWebsites() || $rule->getScopeStoreviews()) {
                    $storeIds = $rule->getScopeStoreviews();

                    if ($websites = $rule->getScopeWebsites()) {
                        foreach ($websites as $websiteId) {
                            $website = $this->website->load($websiteId);
                            $storeIds = $website->getStoreIds();
                        }
                    }

                    $storeIds = array_unique(array_values($storeIds));

                    foreach ($storeIds as $storeId) {
                        /** @var \Magento\Store\Model\Store $store */
                        $store = $this->store->load($storeId);
                        if ($categoryRoot = $store->getRootCategoryId()) {
                            $rootCategories[] = $categoryRoot;
                        }
                    }
                }

                if ($rootCategories) {
                    $rootCategories = array_unique($rootCategories);
                    $allRootCategoryIds = $this->getRootCategoryIds($collection);
                    $deniedCategories = array_diff($allRootCategoryIds, $rootCategories);

                    if ($deniedCategories) {
                        $collection->getSelect()
                            ->where('e.entity_id NOT IN (?)', $deniedCategories);

                        foreach ($deniedCategories as $id) {
                            $collection->getSelect()->where('e.path NOT LIKE ?', '%/' . $id . '/%');
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $collection
     * @param \Amasty\Rolepermissions\Model\Rule $rule
     */
    public function addAttributesFilter($collection, $rule)
    {
        $ruleAttributes = $rule->getAttributes();

        if ($ruleAttributes && !$this->registry->registry('its_amrolepermissions')) {
            if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection) {
                $collection->addFieldToFilter('main_table.attribute_id', ['in' => $ruleAttributes]);
            }
        }
    }

    /**
     * @param Select $select
     *
     * @return bool|int|string
     * @throws \Zend_Db_Select_Exception
     */
    protected function getMainAlias(Select $select)
    {
        $from = $select->getPart(\Zend_Db_Select::FROM);

        foreach ($from as $alias => $data) {
            if ($data['joinType'] == 'from') {
                return $alias;
            }
        }

        return false;
    }

    /**
     * Get all root categories id
     *
     * @param $collection
     *
     * @return array
     */
    private function getRootCategoryIds($collection)
    {
        $connection = $collection->getConnection();
        $select = $connection->select()->from(
            $collection->getMainTable(),
            'entity_id'
        )->where('parent_id = ?', Category::TREE_ROOT_ID);

        return $connection->fetchCol($select);
    }
}
