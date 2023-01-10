<?php
/**
 * Alpine_CustomerCompany
 *
 * @category    Alpine
 * @package     Alpine_CustomerCompany
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\CustomerCompany\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Customer\Model\Customer;
use Alpine\CustomerCompany\Model\Entity\Attribute\Source\Table;

/**
 * Alpine\CustomerCompany\Setup\UpgradeData
 *
 * @category    Alpine
 * @package     Alpine_CustomerCompany
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Eav setup factory
     *
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;
    
    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Upgrade data
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.1.1') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $this->changeCustomerCategorySourceModel($eavSetup);
        }
    }
    
    /**
     * Change customer category source model
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    protected function changeCustomerCategorySourceModel(EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(
            Customer::ENTITY,
            'customer_category',
            'source_model',
            Table::class
        );
    }
}
