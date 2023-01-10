<?php
/**
 * Alpine_ProductVideo
 *
 * @category    Alpine
 * @package     Alpine_ProductVideo
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\ProductVideo\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Alpine\ProductVideo\Setup\InstallData
 *
 * @category    Alpine
 * @package     Alpine_ProductVideo
 */
class InstallData implements InstallDataInterface
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
     * Install data
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        
        $videoAttributes = [
            'video1_url' => 'Video 1',
            'video2_url' => 'Video 2',
            'video3_url' => 'Video 3'
        ];

        foreach ($videoAttributes as $code => $label) {
            if ($eavSetup->getAttribute(Product::ENTITY, $code, 'attribute_code')) {
                $eavSetup->removeAttribute(Product::ENTITY, $code);
            }
            
            $attributeData = [
                'label'                      => $label,
                'type'                       => 'varchar',
                'input'                      => 'text',
                'global'                     => 1,
                'visible'                    => true,
                'required'                   => false,
                'searchable'                 => false,
                'visible_in_advanced_search' => false,
                'filterable'                 => false,
                'filterable_in_search'       => false,
                'comparable'                 => false,
                'used_for_promo_rules'       => false,
                'html_allowed_on_front'      => false,
                'visible_on_front'           => false,
                'used_in_product_listing'    => false,
                'used_for_sort_by'           => false,
                'system'                     => 1,
                'group'                      => 'Accuride Video',
                'apply_to'                   => ''
            ];
            
            $eavSetup->addAttribute(
                Product::ENTITY,
                $code,
                $attributeData
            );
        }
    }
}
