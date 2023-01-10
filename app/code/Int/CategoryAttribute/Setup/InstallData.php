<?php
/**
 * @author Indusnet Team
 * @package Int_CategoryAttribute
 */
namespace Int\CategoryAttribute\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{

    private $eavSetupFactory;
    /**
     * Constructor
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

       $eavSetup->addAttribute(
    \Magento\Catalog\Model\Category::ENTITY,
    'category_sidebar_content',
    [
        'type' => 'text',
        'label' => 'Category Sidebar Content',
        'input' => 'textarea',
        'required' => false,
        'sort_order' => 4,
        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
        'wysiwyg_enabled' => true,
        'is_html_allowed_on_front' => true,
        'group' => 'General Information',
    ]
);
    }
}