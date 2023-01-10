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

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\Entity\Attribute\SetFactory;

/**
 * Alpine\CustomerCompany\Setup\InstallData
 *
 * @category    Alpine
 * @package     Alpine_CustomerCompany
 */
class InstallData implements InstallDataInterface
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
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $this->createCustomerAttributes($eavSetup);
    }
    
    /**
     * Create customer attributes
     *
     * @param EavSetup $eavSetup
     */
    protected function createCustomerAttributes(EavSetup $eavSetup)
    {
        $customerEntity = $this->eavConfig->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeGroupId = $this->setFactory->create()
            ->getDefaultGroupId($attributeSetId);
        
        $attributes = [
            'customer_category' => [
                'label'    => 'Customer Category',
                'type'     => 'varchar',
                'input'    => 'multiselect',
                'backend'  => ArrayBackend::class,
                'source'   => Table::class,
                'position' => 200,
                'options'  => [
                    'Independent',
                    'Professional',
                    'Business'
                ]
            ],
            'pick_your_industry' => [
                'label'    => 'Pick Your Industry',
                'type'     => 'int',
                'input'    => 'select',
                'source'   => Table::class,
                'position' => 210,
                'options'  => [
                    'Architectural/Design',             'Cabinetmaker',
                    'DIY/Homeowners',                   'Engineer',
                    'Industrial/Engineering',           'Maintenance & Repair',
                    'OEM',                              'Retailer- Cabinetry/Woodworking',
                    'Retailer -Industrial/Engineering', 'Specialized/Custom',
                    'Woodworker',                       'Other'
                ]
            ],
            'other_industry' => [
                'label'    => 'Other Industry',
                'type'     => 'varchar',
                'input'    => 'text',
                'position' => 220,
                'required' => false
            ]
        ];

        foreach ($attributes as $code => $attributeInfo) {
            $attributeData = [
                'visible'      => true,
                'required'     => true,
                'user_defined' => true,
                'system'       => 0
            ];
            
            foreach ($attributeInfo as $field => $value) {
                if ($field == 'options') {
                    $attributeData['option']['values'] = $value;
                } else {
                    $attributeData[$field] = $value;
                }
            }
            
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
}
