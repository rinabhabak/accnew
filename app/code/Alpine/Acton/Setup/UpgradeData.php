<?php
/**
 * Alpine_Customer
 *
 * @category    Alpine
 * @package     Alpine_Acton
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\Acton\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory;

/**
 * Alpine\Acton\Setup\InstallData
 *
 * @category    Alpine
 * @package     Alpine_Acton
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV Setup Factory
     *
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;
    
    /**
     * Attribute Set Factory
     *
     * @var SetFactory
     */
    protected $setFactory;
    
    /**
     * EAV Config
     *
     * @var Config
     */
    protected $eavConfig;
    
    /**
     * UpgradeData constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param Config $eavConfig
     * @param SetFactory $setFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        SetFactory $setFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->setFactory = $setFactory;
    }

    /**
     * Install data
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.2.0', '<')) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $this->createCustomerAttribute($eavSetup);
        }
    }
    
    /**
     * Create customer attributes
     *
     * @param EavSetup $eavSetup
     */
    protected function createCustomerAttribute(EavSetup $eavSetup)
    {
        $customerEntity = $this->eavConfig->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeGroupId = $this->setFactory->create()
            ->getDefaultGroupId($attributeSetId);
        
        $code = 'newsletter';
        
        $attributeData = [
            'label'    => 'Subscribe For Newsletter',
            'type'     => 'int',
            'input'    => 'boolean',
            'backend'  => '',
            'frontend' => '',
            'source'   => '',
            'position' => 100,
            'visible'  => true,
            'required' => false,
            'user_defined' => true,
            'default'  => '0',
            'system' => 0,
        ];

        
        $eavSetup->addAttribute(
            Customer::ENTITY,
            $code,
            $attributeData
        );
            
        $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, $code);
        $attribute->addData([
            'used_in_forms'      => ['adminhtml_customer', 'customer_account_create'],
            'validate_rules'     => [],
            'attribute_set_id'   => $attributeSetId,
            'attribute_group_id' => $attributeGroupId
        ]);
        $attribute->save();
    }
}
