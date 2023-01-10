<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.2') < 0) {

            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            /**
             * Add attributes to the eav/attribute
             */
            $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $eavSetup->removeAttribute($entityTypeId, 'snippet_title');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'snippet_title',
                [
                    'group' => 'Richsnippets',
                    'type' => 'varchar',
                    'input' => 'text',
                    'label' => 'Snippet title',
                    'required' => false,
                    'sort_order' => 1,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true
                ]
            );

            $eavSetup->removeAttribute($entityTypeId, 'snippet_description');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'snippet_description',
                [
                    'group' => 'Richsnippets',
                    'type' => 'text',
                    'label' => 'Snippet Description',
                    'input' => 'textarea',
                    'required' => false,
                    'note' => 'Maximum 255 chars',
                    'class' => 'validate-length maximum-length-255',
                    'sort_order' => 3,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true
                ]
            );

            $eavSetup->removeAttribute($entityTypeId, 'snippet_image');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'snippet_image',
                [
                    'group' => 'Richsnippets',
                    'type' => 'varchar',
                    'label' => 'Snippet Image',
                    'input' => 'media_image',
                    'frontend' => 'Magento\Catalog\Model\Product\Attribute\Frontend\Image',
                    'required' => false,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true
                ]
            );
        }

        $setup->endSetup();
    }
}