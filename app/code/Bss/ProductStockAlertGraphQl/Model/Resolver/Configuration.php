<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlertGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ProductStockAlertGraphQl\Model\Resolver;

use Bss\ProductStockAlert\Helper\Data;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Configuration implements ResolverInterface
{
    /**
     * const
     */
    const XML_PATH_STOCK_ALLOW = 'allow_stock';
    const XML_PATH_CUSTOMER_ALLOW = 'allow_customer';
    const XML_PATH_EMAIL_SEND_BASED_QTY = 'email_based_qty';
    const XML_PATH_SEND_LIMIT = 'send_limit';
    const XML_PATH_QTY_ALLOW = 'allow_stock_qty';
    const XML_PATH_NOTIFICATION_MESSAGE = 'message';
    const XML_PATH_STOP_NOTIFICATION_MESSAGE = 'stop_message';
    const XML_BUTTON_DESIGN_BUTTON_TEXT = 'button_text';
    const XML_BUTTON_DESIGN_STOP_BUTTON_TEXT = 'stop_button_text';
    const XML_BUTTON_DESIGN_BUTTON_TEXT_COLOR = 'button_text_color';
    const XML_BUTTON_DESIGN_BUTTON_COLOR = 'button_color';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Configuration constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param ValueFactory $valueFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        GroupRepositoryInterface $groupRepository,
        ValueFactory $valueFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->groupRepository = $groupRepository;
        $this->valueFactory = $valueFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed
     * @throws GraphQlInputException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        if (!isset($args['store_id'])) {
            throw new GraphQlInputException(__('Invalid store_id'));
        }
        $storeId = (int)$args['store_id'];
        try {
            $store = $this->storeManager->getStore($storeId);
        } catch (NoSuchEntityException $noSuchEntityException) {
            throw new GraphQlNoSuchEntityException(__($noSuchEntityException->getMessage()));
        } catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__('Store ID is invalid'));
        }

        $configuration = [];
        $configuration[self::XML_PATH_STOCK_ALLOW] = (bool)$this->scopeConfig->getValue(
            Data::XML_PATH_STOCK_ALLOW,
            ScopeInterface::SCOPE_WEBSITE
        );
        $allowCustomer = $this->scopeConfig->getValue(
            Data::XML_PATH_CUSTOMER_ALLOW,
            ScopeInterface::SCOPE_WEBSITE
        );

        $filter = $this->filterBuilder
            ->setValue(explode(',', $allowCustomer))
            ->setField('customer_group_id')
            ->setConditionType('in')
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter])->create();
        $customerGroups = $this->groupRepository->getList($searchCriteria);
        $customerGroupsArr = [];
        /** @var GroupInterface $group */
        array_map(function ($group) use (&$customerGroupsArr) {
            $customerGroupsArr[] = $group->getCode();
        }, $customerGroups->getItems());

        $configuration[self::XML_PATH_CUSTOMER_ALLOW] = $customerGroupsArr;
        $configuration[self::XML_PATH_EMAIL_SEND_BASED_QTY] = (bool)$this->scopeConfig->getValue(
            Data::XML_PATH_EMAIL_SEND_BASED_QTY,
            ScopeInterface::SCOPE_WEBSITE
        );
        $configuration[self::XML_PATH_NOTIFICATION_MESSAGE] = $this->scopeConfig->getValue(
            Data::XML_PATH_NOTIFICATION_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
        $configuration[self::XML_PATH_STOP_NOTIFICATION_MESSAGE] = $this->scopeConfig->getValue(
            Data::XML_PATH_STOP_NOTIFICATION_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
        $configuration[self::XML_PATH_SEND_LIMIT] = (int)$this->scopeConfig->getValue(
            Data::XML_PATH_SEND_LIMIT,
            ScopeInterface::SCOPE_WEBSITE
        );
        $configuration[self::XML_PATH_QTY_ALLOW] = (int)$this->scopeConfig->getValue(
            Data::XML_PATH_QTY_ALLOW,
            ScopeInterface::SCOPE_WEBSITE
        );
        $configuration[self::XML_BUTTON_DESIGN_BUTTON_TEXT] = $this->scopeConfig->getValue(
            Data::XML_BUTTON_DESIGN_BUTTON_TEXT,
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
        $configuration[self::XML_BUTTON_DESIGN_STOP_BUTTON_TEXT] = $this->scopeConfig->getValue(
            Data::XML_BUTTON_DESIGN_STOP_BUTTON_TEXT,
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
        $configuration[self::XML_BUTTON_DESIGN_BUTTON_TEXT_COLOR] = $this->scopeConfig->getValue(
            Data::XML_BUTTON_DESIGN_BUTTON_TEXT_COLOR,
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
        $configuration[self::XML_BUTTON_DESIGN_BUTTON_COLOR] = $this->scopeConfig->getValue(
            Data::XML_BUTTON_DESIGN_BUTTON_COLOR,
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
        return $this->valueFactory->create(function () use ($configuration) {
            return $configuration;
        });
    }
}
