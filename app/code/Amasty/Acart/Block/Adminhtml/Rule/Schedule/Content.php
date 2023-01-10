<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

declare(strict_types=1);

namespace Amasty\Acart\Block\Adminhtml\Rule\Schedule;

use Amasty\Acart\Model\ResourceModel\Schedule\Collection as ScheduleCollection;
use Amasty\Acart\Model\Rule;
use Amasty\Acart\Model\Schedule;
use Amasty\Base\Model\ModuleInfoProvider;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Email\Model\ResourceModel\Template\Collection;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory as TemplatesCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as SalesRuleCollection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Store\Model\ScopeInterface;

class Content extends Template
{
    const CRON_FAQ = 'https://amasty.com/blog/configure-magento-cron-job?utm_source=extension&utm_medium=tooltip'
        .'&utm_campaign=abandoned-cart-m2-cron-recommended-settings';
    const MAX_SALES_RULES = 100;
    const QUOTE_LIFETIMES_CONFIG_PATH = 'checkout/cart/delete_quote_after';

    protected $_template = 'rule/schedule.phtml';

    /**
     * @var SalesRuleCollection
     */
    protected $salesRuleCollection;

    /**
     * @var Collection
     */
    protected $emailTemplateCollection;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var ScopeConfigInterface
     */
    private $storesConfig;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(
        Context $context,
        TemplatesCollectionFactory $templatesCollectionFactory,
        Registry $registry,
        CollectionFactory $ruleCollectionFactory,
        ModuleInfoProvider $moduleInfoProvider,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->emailTemplateCollection = $templatesCollectionFactory->create()
            ->addFilter('orig_template_code', 'amasty_acart_template');
        $this->salesRuleCollection = $ruleCollectionFactory->create()
            ->addFilter('use_auto_generation', 1)
            ->addFilter('is_active', 1);

        $this->storesConfig = $context->getScopeConfig();
        $this->moduleInfoProvider = $moduleInfoProvider;

        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
    }

    public function getAddRecordButtonHtml(): string
    {
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'label' => __('Add Record'),
                'onclick' => 'return amastyAcartSchedule.addItem();',
                'class' => 'add amasty-add-row'
            ]
        );
        $button->setName('add_record_button');

        return $button->toHtml();
    }

    public function quoteLifetimeNoticeIsAvailable(): bool
    {
        $quoteLifetimes = $this->storesConfig->getValue(
            self::QUOTE_LIFETIMES_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
        $scheduleCollection = $this->getScheduleCollection();

        if ($scheduleCollection->getSize() > 0 && $quoteLifetimes) {
            /** @var Schedule $schedule */
            foreach ($scheduleCollection->load() as $schedule) {
                if ($schedule->getDays() >= $quoteLifetimes) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getNumberOptions(int $number): string
    {
        $ret = ['<option value="">-</option>'];
        for ($index = 1; $index <= $number; $index++) {
            $ret[] = '<option value="' . $index . '" >' . $index . '</option>';
        }

        return implode('', $ret);
    }

    public function getEmailTemplateCollection(): Collection
    {
        return $this->emailTemplateCollection;
    }

    public function getSalesRuleCollection(): SalesRuleCollection
    {
        return $this->salesRuleCollection;
    }

    public function isShowSalesRuleSelect(): bool
    {
        return $this->salesRuleCollection->getSize() < self::MAX_SALES_RULES;
    }

    public function getScheduleCollection(): ScheduleCollection
    {
        $rule = $this->coreRegistry->registry(Rule::CURRENT_AMASTY_ACART_RULE);

        return $rule->getScheduleCollection();
    }

    public function moduleEnabled($module)
    {
        return $this->moduleManager->isEnabled($module);
    }

    public function getCronUrl(): string
    {
        return self::CRON_FAQ;
    }

    public function getPromotionExtensionUrl(): string
    {
        if ($this->moduleInfoProvider->isOriginMarketplace()) {
            return "https://marketplace.magento.com/amasty-module-special-promotions.html";
        }

        return "https://amasty.com/special-promotions-pro-for-magento-2.html"
            . "?utm_source=extension&utm_medium=link&utm_campaign=acart-spp-m2";
    }
}
