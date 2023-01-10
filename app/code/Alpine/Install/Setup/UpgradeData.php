<?php
/**
 * Alpine_Install - Upgrade Data
 *
 * @category    Alpine
 * @package     Alpine_Install
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Aleksandr Mikhailov <aleksandr.mikhailov@alpineinc.com>
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 * @author      EvgeniyDerevyanko <evgeniy.derevyanko@alpineinc.com>
 */
namespace Alpine\Install\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Frontend\Image;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\Store;
use Amasty\ProductAttachment\Helper\Config;

/**
 * Class UpgradeData
 *
 * @category    Alpine
 * @package     Alpine_Install
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
     * EAV Setup 
     *
     * @var EavSetup
     */
    protected $eavSetup;

    protected $productAttributeRepository;
    protected $categoryPosition = [
        'length' => 0,
        'side_space' => 1,
        'load_rating' => 2,
        'extension' => 3,
        'mounting' => 4,
        'special_features' => 5,
        'finish' => 6,
        'material' => 7,
        'custom_category' => 8,
        'market' => 9,
        'price' => 10
    ];
    protected $categoryFilterable = [
        'disconnect' => 0,
        'handed' => 0
    ];
    
    /**
     * Config writer
     *
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * UpgradeData constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param WriterInterface $configWriter
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        WriterInterface $configWriter,
            EavSetup $eavSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->configWriter = $configWriter;
        $this->eavSetup = $eavSetup;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.2.0') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $this->createTopChoiceAttribute($eavSetup);
        }

        if (version_compare($context->getVersion(), '0.3.0') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $this->createCrossSectionImageAttribute($eavSetup);
        }

        if (version_compare($context->getVersion(), '0.4.0') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $this->createAccurideAdditionalProductAttributes($eavSetup);
        }

        if (version_compare($context->getVersion(), '0.5.0') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $this->createProductSectionSwitcherAttribute($eavSetup, $setup);
        }

        if (version_compare($context->getVersion(), '0.7.0') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $this->addApplicationsAttribute($eavSetup);
        }

        if (version_compare($context->getVersion(), '0.8.0') < 0) {
            $this->configWriter->save(
                Config::BLOCK_LABEL,
                'Downloads'
            );
        }

        $setup->endSetup();

        if (version_compare($context->getVersion(), '0.9.0') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            // Do not use startSetup and endSetup for that method
            $this->createAllProductAttributes($eavSetup);
        }

        if (version_compare($context->getVersion(), '0.10.0') < 0) {
            $this->configWriter->save(
                'btob/website_configuration/negotiablequote_active',
                true
            );
        }

        if (version_compare($context->getVersion(), '0.11.0') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            // Do not use startSetup and endSetup for that method
            $this->updateSeveralAttributes($eavSetup);
        }

        if (version_compare($context->getVersion(), '0.11.1') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            // Do not use startSetup and endSetup for that method
            $this->updateApplicationsAttribute($eavSetup);
        }

        if (version_compare($context->getVersion(), '0.11.2') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            // Do not use startSetup and endSetup for that method
            $this->updateLengthAndSideSpaceAttributes($eavSetup);
        }

        if (version_compare($context->getVersion(), '0.11.3') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            // Do not use startSetup and endSetup for that method
            $this->recreateSlideSelectorAttributes($eavSetup);
        }

        if (version_compare($context->getVersion(), '0.11.4') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            // Do not use startSetup and endSetup for that method
            $this->updateProductFeaturesAttribute($eavSetup);
        }

        if (version_compare($context->getVersion(), '0.12.0') < 0) {
            $this->configWriter->save(
                Store::XML_PATH_USE_REWRITES,
                true
            );

            $this->configWriter->save(
                ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
                null
            );

            $this->configWriter->save(
                CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
                null
            );
        }

        if (version_compare($context->getVersion(), '0.12.2') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $this->hideProductAttributes($eavSetup);
        }

        if (version_compare($context->getVersion(), '0.12.3') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $this->updateTopChoiceAttribute($eavSetup);
        }

        if (version_compare($context->getVersion(), '0.12.4') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $this->updateUomAttribute($eavSetup);
        }
        if (version_compare($context->getVersion(), '0.12.5') < 0) {
            $setup->startSetup();
            $this->eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'product_for_sales', 'source_model', Boolean::class, null); 
            $setup->endSetup(); 
        }
    }

    /**
     * Create "is_top_choice" Product Attribute
     *
     * @param EavSetup $eavSetup
     */
    protected function createTopChoiceAttribute(EavSetup $eavSetup)
    {
        $groupName     = 'General';
        $attributeCode = 'is_top_choice';

        if ($eavSetup->getAttribute(Product::ENTITY, $attributeCode, 'attribute_code')) {
            return;
        }

        $eavSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            [
                'type'                    => 'int',
                'label'                   => 'Top Choice',
                'input'                   => 'boolean',
                'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'                  => Boolean::class,
                'visible'                 => true,
                'required'                => false,
                'user_defined'            => true,
                'default'                 => Boolean::VALUE_NO,
                'default_value'           => Boolean::VALUE_NO,
                'searchable'              => true,
                'filterable'              => true,
                'is_used_in_grid'         => true,
                'is_filterable_in_grid'   => true,
                'comparable'              => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => true,
                'unique'                  => false,
                'system'                  => 1,
                'group'                   => $groupName,
                'apply_to'                => '',
                'position'                => 100,
            ]
        );
    }

    /**
     * Create "Cross Section Image" attribute
     *
     * @param EavSetup $eavSetup
     */
    protected function createCrossSectionImageAttribute(EavSetup $eavSetup)
    {
        $groupName     = 'Image Management';
        $attributeCode = 'cross_section_image';

        if ($eavSetup->getAttribute(Product::ENTITY, $attributeCode, 'attribute_code')) {
            return;
        }

        $eavSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            [
                'type'                    => 'varchar',
                'label'                   => 'Cross Section Image',
                'input'                   => 'media_image',
                'frontend'                => Image::class,
                'required'                => false,
                'global'                  => ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'group'                   => $groupName,
            ]
        );
    }

    /**
     * Create additional product attributes fot Accuride (with attribute group)
     *
     * @param EavSetup $eavSetup
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createAccurideAdditionalProductAttributes(EavSetup $eavSetup)
    {
        $groupName       = 'Accuride attributes';
        $entityTypeId    = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);

        foreach ($attributeSetIds as $attributeSetId) {
            if ($eavSetup->getAttributeGroup($entityTypeId, $attributeSetId, $groupName, 'attribute_group_id')) {
                continue;
            }
            $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 100);
        }

        $selectAttributes = [
            'mounting'          => [
                'label'    => 'Mounting',
                'position' => 0,
                'options'  => ['Side', 'Under', 'Flat', 'Bayonet', 'Rail'],
            ],
            'load'              => [
                'label'    => 'Load',
                'position' => 1,
                'options'  => [
                    'Heavy Duty (170 LB. - 1323 LB.)',
                    'Medium Duty (140 LB. - 169 LB.)',
                    'Light Duty (139 LB. or less)',
                ],
            ],
            'side_space'        => [
                'label'    => 'Side Space',
                'position' => 2,
                'options'  => ['6"', '8"', '10"'],
            ],
            'length'            => [
                'label'    => 'Length',
                'position' => 3,
                'options'  => ['6"', '8"', '10"', '12"', '13"', '14"'],
            ],
            'extension'         => [
                'label'    => 'Extension',
                'position' => 4,
                'options'  => ['Full', 'Partial', 'Over'],
            ],
            'special_features'  => [
                'label'    => 'Special Features',
                'position' => 5,
                'options'  => [
                    'Touch Release',
                    'Easy Close',
                    'Self Close',
                    'Detend Out',
                    'Lock-In/Lock-Out',
                ],
            ],
            'finishes'          => [
                'label'    => 'Finishes',
                'position' => 6,
                'options'  => [
                    'Clear Zinc',
                    'Black Zinc',
                    'White',
                    'Stain Steel',
                ],
            ],
            'weather_resistant' => [
                'label'    => 'Weather-Resistant',
                'position' => 7,
                'options'  => [
                    'Standard',
                    'Good',
                    'Better',
                    'Best',
                ],
            ],
        ];

        foreach ($selectAttributes as $code => $fieldInfo) {
            if ($eavSetup->getAttribute(Product::ENTITY, $code, 'attribute_code')) {
                continue;
            }
            $eavSetup->addAttribute(
                Product::ENTITY,
                $code,
                [
                    'type'                    => 'int',
                    'label'                   => $fieldInfo['label'],
                    'input'                   => 'select',
                    'global'                  => 1,
                    'visible'                 => true,
                    'required'                => false,
                    'user_defined'            => true,
                    'default'                 => null,
                    'searchable'              => true,
                    'filterable'              => true,
                    'is_used_in_grid'         => true,
                    'is_filterable_in_grid'   => true,
                    'filterable_in_search'    => true,
                    'comparable'              => true,
                    'visible_on_front'        => true,
                    'used_in_product_listing' => true,
                    'unique'                  => false,
                    'system'                  => 1,
                    'group'                   => $groupName,
                    'apply_to'                => '',
                    'position'                => $fieldInfo['position'],
                    'option'                  => [
                        'values' => $fieldInfo['options']
                    ]
                ]
            );
        }
    }

    /**
     * Create section switcher ("Available Online" / "Other Products") for products
     *
     * @param EavSetup                 $eavSetup
     * @param ModuleDataSetupInterface $setup
     */
    protected function createProductSectionSwitcherAttribute(EavSetup $eavSetup, ModuleDataSetupInterface $setup)
    {
        $code = 'product_for_sales';

        if ($eavSetup->getAttribute(Product::ENTITY, $code, 'attribute_code')) {
            return;
        }

        $eavSetup->addAttribute(
            Product::ENTITY,
            $code,
            [
                'type'                    => 'int',
                'label'                   => 'Product For Sales',
                'input'                   => 'select',
                'global'                  => 0,
                'visible'                 => true,
                'required'                => true,
                'user_defined'            => true,
                'default'                 => 1,
                'searchable'              => false,
                'filterable'              => true,
                'is_used_in_grid'         => true,
                'is_filterable_in_grid'   => true,
                'filterable_in_search'    => false,
                'comparable'              => false,
                'visible_on_front'        => true,
                'used_in_product_listing' => true,
                'unique'                  => false,
                'system'                  => 1,
                'group'                   => 'General',
                'apply_to'                => '',
                'option'                  => [
                    'values' => [
                        0 => __('No'),
                        1 => __('Yes'),
                    ]
                ]
            ]
        );

        $this->updateDefaultValue($setup, $code, __('Yes')->getText());
    }

    /**
     * Update a "default value" for dropdown attribute
     *
     * @param ModuleDataSetupInterface $setup
     * @param                          $attributeCode
     * @param                          $defaultValue
     */
    protected function updateDefaultValue(ModuleDataSetupInterface $setup, $attributeCode, $defaultValue)
    {
        $connection = $setup->getConnection();

        $attrTable = $connection->getTableName('eav_attribute');
        $select    = $connection->select()
            ->from($attrTable)
            ->where('attribute_code = :attribute_code');

        $attribute = $connection->fetchRow($select, ['attribute_code' => $attributeCode]);

        $optionsTable       = $connection->getTableName('eav_attribute_option');
        $optionsValuesTable = $connection->getTableName('eav_attribute_option_value');

        $select = $connection->select()
            ->from(['o' => $optionsTable])
            ->joinLeft(
                ['ov' => $optionsValuesTable],
                'o.option_id = ov.option_id',
                'value'
            )
            ->where('o.attribute_id = :attribute_id');

        $options = $connection->fetchAll($select, ['attribute_id' => $attribute['attribute_id']]);

        // try to find option by label
        $defaultOption = null;
        foreach ($options as $option) {
            if ($option['value'] === $defaultValue) {
                $defaultOption = $option;
                break;
            }
        }

        // if option not found (by label) then the first option will use as default
        if (empty($defaultOption)) {
            $defaultOption = current($options);
        }

        $connection->update(
            $attrTable,
            ['default_value' => $defaultOption['option_id']],
            ['attribute_code =?' => $attributeCode]
        );
    }

    /**
     * Create additional product attributes fot Accuride (with attribute group) - prototype
     *
     * @param EavSetup $eavSetup
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createAccurideAdditionalProductAttributesPrototype(EavSetup $eavSetup)
    {
        $groupName       = 'Accuride attributes';
        $entityTypeId    = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);

        foreach ($attributeSetIds as $attributeSetId) {
            if ($eavSetup->getAttributeGroup($entityTypeId, $attributeSetId, $groupName, 'attribute_group_id')) {
                print_r('group ' . $groupName . ' already exists' . PHP_EOL);
                continue;
            }
            $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 100);
        }

        $priceAttributes = [
            'cost' => [
                'label'    => 'Cost',
                //'apply_to' => 'simple',
                'position' => 7,
            ],
        ];

        $textfieldAttributes = [
            'family'              => [
                'label'    => 'Family',
                //'apply_to' => 'configurable',
                'position' => 1,
            ],
            'mpn'                 => [
                'label'    => 'MPN',
                //'apply_to' => 'simple,configurable',
                'position' => 2,
            ],
            'upc'                 => [
                'label'    => 'UPC',
                //'apply_to' => 'configurable',
                'position' => 4,
            ],
            'barcode'             => [
                'label'    => 'Barcode',
                //'apply_to' => 'configurable',
                'position' => 5,
            ],
            'slides_p_box'        => [
                'label'    => 'Slides P. Box',
                //'apply_to' => 'simple',
                'position' => 6,
            ],
            'ch_list_pair'        => [
                'label'    => 'CH List/Pair',
                //'apply_to' => 'simple',
                'position' => 8,
            ],
            'ch_list_each'        => [
                'label'    => 'CH list/each',
                //'apply_to' => 'simple',
                'position' => 9,
            ],
            'ie_list_each'        => [
                'label'    => 'IE list/each',
                //'apply_to' => 'simple',
                'position' => 10,
            ],
            'ie_list_pair'        => [
                'label'    => 'IE list/pair',
                //'apply_to' => 'simple',
                'position' => 11,
            ],
            'accu_stock_ch'       => [
                'label'    => 'Accu Stock CH',
                //'apply_to' => 'simple',
                'position' => 12,
            ],
            'accu_stock_ie'       => [
                'label'    => 'Accu Stock IE',
                //'apply_to' => 'simple',
                'position' => 13,
            ],
            'order_policy'        => [
                'label'    => 'Order Policy',
                //'apply_to' => 'simple',
                'position' => 15,
            ],
            'note'                => [
                'label'    => 'Note',
                //'apply_to' => 'simple',
                'position' => 16,
            ],
            'ie_net'              => [
                'label'    => 'IE Net',
                //'apply_to' => 'simple',
                'position' => 17,
            ],
            'ch_net_price'        => [
                'label'    => 'CH Net Price',
                //'apply_to' => 'simple',
                'position' => 18,
            ],
            'notify_if_below_qty' => [
                'label'    => 'Notification if Below Qty',
                //'apply_to' => 'simple,configurable',
                'position' => 19,
            ],
            'reorder_qty'         => [
                'label'    => 'Reorder Qty',
                //'apply_to' => 'simple,configurable',
                'position' => 20,
            ],
            'disconnect'          => [
                'label'    => 'Disconnect',
                //'apply_to' => 'configurable',
                'position' => 22,
            ],
            'extension'           => [
                'label'    => 'Extension',
                //'apply_to' => 'configurable',
                'position' => 23,
            ],
            'finish'              => [
                'label'    => 'Finish',
                //'apply_to' => 'configurable',
                'position' => 25,
            ],
            'load_rating'         => [
                'label'    => 'Load Rating',
                //'apply_to' => 'configurable',
                'position' => 26,
            ],
            'industry'            => [
                'label'    => 'Industry',
                //'apply_to' => 'configurable',
                'position' => 27,
            ],
            'market'              => [
                'label'    => 'Market',
                //'apply_to' => 'configurable',
                'position' => 28,
            ],
            'application'         => [
                'label'    => 'Application',
                //'apply_to' => 'configurable',
                'position' => 29,
            ],
        ];

        $textareaAttributes = [
            'd_pack_pricing_includes' => [
                'label'    => 'D pack Pricing includes',
                //'apply_to' => 'configurable',
                'position' => 14,
            ],
            'specifications'          => [
                'label'    => 'Specifications',
                //'apply_to' => 'configurable',
                'position' => 21,
            ],
            'special_feature'         => [
                'label'    => 'Special Feature',
                //'apply_to' => 'configurable',
                'position' => 24,
            ],
            'optional_kits'           => [
                'label'    => 'Optional Kits',
                //'apply_to' => 'configurable',
                'position' => 30,
            ],
            'technical_links'         => [
                'label'    => 'Technical Links',
                //'apply_to' => 'simple,configurable',
                'position' => 34,
            ],
        ];

        $yesNoAttributes = [
            'rohs' => [
                'label'    => 'Rohs',
                //'apply_to' => 'configurable',
                'position' => 31,
            ],
            'bhma' => [
                'label'    => 'BHMA',
                //'apply_to' => 'configurable',
                'position' => 32,
            ],
            'awi'  => [
                'label'    => 'AWI',
                //'apply_to' => 'configurable',
                'position' => 33,
            ],
        ];

        $selectAttributes = [
            'length' => [
                'label'    => 'Length',
                //'apply_to' => 'simple',
                'position' => 3,
                'options'  => ['12', '14', '15', '16', '18', '20', '21', '22', '24', '26', '28'],
            ]
        ];

        foreach ($textfieldAttributes as $code => $fieldInfo) {
            if ($eavSetup->getAttribute(Product::ENTITY, $code, 'attribute_code')) {
                print_r('attribute ' . $code . ' already exists' . PHP_EOL);
                continue;
            }
            $eavSetup->addAttribute(
                Product::ENTITY,
                $code,
                [
                    'type'                    => 'varchar',
                    'label'                   => $fieldInfo['label'],
                    'input'                   => 'text',
                    'global'                  => 1,
                    'visible'                 => true,
                    'required'                => false,
                    'user_defined'            => true,
                    'default'                 => null,
                    'searchable'              => false,
                    'filterable'              => true,
                    'is_used_in_grid'         => true,
                    'is_filterable_in_grid'   => true,
                    'comparable'              => false,
                    'visible_on_front'        => false,
                    'used_in_product_listing' => false,
                    'unique'                  => false,
                    'system'                  => 1,
                    'group'                   => $groupName,
                    'apply_to'                => isset($fieldInfo['apply_to']) ? $fieldInfo['apply_to'] : '',
                    'position'                => $fieldInfo['position'],
                ]
            );
        }

        foreach ($textareaAttributes as $code => $fieldInfo) {
            if ($eavSetup->getAttribute(Product::ENTITY, $code, 'attribute_code')) {
                print_r('attribute ' . $code . ' already exists' . PHP_EOL);
                continue;
            }
            $eavSetup->addAttribute(
                Product::ENTITY,
                $code,
                [
                    'type'                    => 'text',
                    'label'                   => $fieldInfo['label'],
                    'input'                   => 'textarea',
                    'global'                  => 1,
                    'visible'                 => true,
                    'required'                => false,
                    'user_defined'            => true,
                    'default'                 => null,
                    'searchable'              => false,
                    'filterable'              => true,
                    'is_used_in_grid'         => true,
                    'is_filterable_in_grid'   => true,
                    'comparable'              => false,
                    'visible_on_front'        => false,
                    'used_in_product_listing' => false,
                    'unique'                  => false,
                    'system'                  => 1,
                    'group'                   => $groupName,
                    'apply_to'                => isset($fieldInfo['apply_to']) ? $fieldInfo['apply_to'] : '',
                    'position'                => $fieldInfo['position'],
                ]
            );
        }

        foreach ($priceAttributes as $code => $fieldInfo) {
            if ($eavSetup->getAttribute(Product::ENTITY, $code, 'attribute_code')) {
                print_r('attribute ' . $code . ' already exists' . PHP_EOL);
                continue;
            }
            $eavSetup->addAttribute(
                Product::ENTITY,
                $code,
                [
                    'type'                    => 'decimal',
                    'label'                   => $fieldInfo['label'],
                    'input'                   => 'price',
                    'global'                  => 1,
                    'visible'                 => true,
                    'required'                => false,
                    'user_defined'            => true,
                    'default'                 => null,
                    'searchable'              => false,
                    'filterable'              => true,
                    'is_used_in_grid'         => true,
                    'is_filterable_in_grid'   => true,
                    'comparable'              => false,
                    'visible_on_front'        => false,
                    'used_in_product_listing' => false,
                    'unique'                  => false,
                    'system'                  => 1,
                    'group'                   => $groupName,
                    'apply_to'                => isset($fieldInfo['apply_to']) ? $fieldInfo['apply_to'] : '',
                    'position'                => $fieldInfo['position'],
                ]
            );
        }

        foreach ($yesNoAttributes as $code => $fieldInfo) {
            if ($eavSetup->getAttribute(Product::ENTITY, $code, 'attribute_code')) {
                print_r('attribute ' . $code . ' already exists' . PHP_EOL);
                continue;
            }
            $eavSetup->addAttribute(
                Product::ENTITY,
                $code,
                [
                    'type'                    => 'int',
                    'label'                   => $fieldInfo['label'],
                    'input'                   => 'boolean',
                    'global'                  => 1,
                    'visible'                 => true,
                    'required'                => false,
                    'user_defined'            => true,
                    'default'                 => null,
                    'searchable'              => false,
                    'filterable'              => true,
                    'is_used_in_grid'         => true,
                    'is_filterable_in_grid'   => true,
                    'comparable'              => false,
                    'visible_on_front'        => false,
                    'used_in_product_listing' => false,
                    'unique'                  => false,
                    'system'                  => 1,
                    'group'                   => $groupName,
                    'apply_to'                => isset($fieldInfo['apply_to']) ? $fieldInfo['apply_to'] : '',
                    'position'                => $fieldInfo['position'],
                ]
            );
        }

        foreach ($selectAttributes as $code => $fieldInfo) {
            if ($eavSetup->getAttribute(Product::ENTITY, $code, 'attribute_code')) {
                print_r('attribute ' . $code . ' already exists' . PHP_EOL);
                continue;
            }
            $eavSetup->addAttribute(
                Product::ENTITY,
                $code,
                [
                    'type'                    => 'int',
                    'label'                   => $fieldInfo['label'],
                    'input'                   => 'select',
                    'global'                  => 1,
                    'visible'                 => true,
                    'required'                => false,
                    'user_defined'            => true,
                    'default'                 => null,
                    'searchable'              => false,
                    'filterable'              => true,
                    'is_used_in_grid'         => true,
                    'is_filterable_in_grid'   => true,
                    'comparable'              => false,
                    'visible_on_front'        => false,
                    'used_in_product_listing' => false,
                    'unique'                  => false,
                    'system'                  => 1,
                    'group'                   => $groupName,
                    'apply_to'                => isset($fieldInfo['apply_to']) ? $fieldInfo['apply_to'] : '',
                    //'apply_to'                => $fieldInfo['apply_to'],
                    'position'                => $fieldInfo['position'],
                    'option'                  => [
                        'values' => $fieldInfo['options']
                    ]
                ]
            );
        }
    }

    /**
     * Remove attributes (for testing)
     *
     * @param EavSetup $eavSetup
     */
    protected function removeAccurideData(EavSetup $eavSetup)
    {
        $attributeCodes = [
            'family',
            'mpn',
            'length',
            'upc',
            'barcode',
            'slides_p_box',
            'cost',
            'ch_list_pair',
            'ch_list_each',
            'ie_list_each',
            'ie_list_pair',
            'accu_stock_ch',
            'accu_stock_ie',
            'd_pack_pricing_includes',
            'order_policy',
            'note',
            'ie_net',
            'ch_net_price',
            'notify_if_below_qty',
            'reorder_qty',
            'specifications',
            'disconnect',
            'extension',
            'special_feature',
            'finish',
            'load_rating',
            'industry',
            'market',
            'application',
            'optional_kits',
            'rohs',
            'bhma',
            'awi',
            'technical_links',
        ];

        foreach ($attributeCodes as $code) {
            $eavSetup->removeAttribute(Product::ENTITY, $code);
        }
    }
    
    /**
     * Create all product attributes for Accuride
     *
     * @param EavSetup $eavSetup
     * @return void
     *
     * @throws LocalizedException
     */
    protected function createAllProductAttributes(EavSetup $eavSetup)
    {
        $groupName = 'Accuride attributes';

        $defaultMagentoAttributes = [
            'name' => [
                'visible_in_advanced_search' => false,
                'comparable'                 => true,
                'used_for_promo_rules'       => true,
                'used_for_sort_by'           => false
            ],
            'description' => [
                'visible_in_advanced_search' => false
            ]
        ];
        
        foreach ($defaultMagentoAttributes as $code => $fieldInfo) {
            if ($eavSetup->getAttribute(Product::ENTITY, $code, 'attribute_code')) {
                foreach ($fieldInfo as $field => $value) {
                    $eavSetup->updateAttribute(
                        Product::ENTITY,
                        $field,
                        $value
                    );
                }
            }
        }
        
        $additionalAttributes = [
            'manufacture' => [
                'label' => 'Manufacture',
                'type' => 'varchar',
                'input' => 'text'
            ],
            'family' => [
                'label' => 'Family',
                'type' => 'varchar',
                'input' => 'text',
                'comparable' => false,
                'visible_on_front' => false
            ],
            'mpn' => [
                'label' => 'MPN',
                'type' => 'varchar',
                'input' => 'text',
                'comparable' => false,
                'visible_on_front' => false
            ],
            'upc' => [
                'label' => 'UPC',
                'type' => 'text',
                'input' => 'textarea',
                'visible_on_front' => false
            ],
            'extension' => [
                'label' => 'Extension',
                'type' => 'int',
                'input' => 'select',
                'visible_on_front' => false,
                'options' => [
                    '3/4 Extension', 'Full Extension', 'Over-Travel', 'Other'
                ]
            ],
            'specifications' => [
                'label' => 'Specifications and Features',
                'type' => 'text',
                'input' => 'textarea',
                'html_allowed_on_front' => true
            ],
            'special_features' => [
                'label' => 'Special Features',
                'type' => 'text',
                'input' => 'multiselect',
                'visible_on_front' => false,
                'backend' => ArrayBackend::class,
                'options' => [
                    'Corrision-Resistance',  'Detent-In',      'Detent-Out',
                    'Easy Close/Soft Close', 'Easy Down',      'Lock-In',
                    'Lock-Out',              'Locking Handle', 'Overhead',
                    'Self-Close',            'Touch Release',  'Two Way Travel',
                    'Interlock'
                ]
            ],
            'length' => [
                'label' => 'Length',
                'type' => 'int',
                'input' => 'select',
                'visible_on_front' => false,
                'options' => [
                    '6"',  '8"',  '10"', '12"', '13"', '14"', '15"', '16"', '17"',
                    '18"', '19"', '20"', '21"', '22"', '23"', '24"', '25"', '26"',
                    '27"', '28"', '30"', '31"', '32"', '34"', '36"', '40"', '42"',
                    '44"', '47"', '48"', '60"', '94"'
                ]
            ],
            'side_space' => [
                'label' => 'Side Space',
                'type' => 'int',
                'input' => 'select',
                'visible_on_front' => false,
                'options' => [
                    'Less than .50', '.50', 'Between .50 and .75',
                    '.75',           'More than .75'
                ]
            ],
            'finish' => [
                'label' => 'Finish',
                'type' => 'int',
                'input' => 'select',
                'options' => [
                    'Zinc', 'White', 'Black', 'Ch'
                ]
            ],
            'load_rating' => [
                'label' => 'Load Rating',
                'type' => 'int',
                'input' => 'select',
                'visible_on_front' => false,
                'options' => [
                   ' 0 - 100 lbs',   '101 - 200 lbs', '201 - 300 lbs',
                    '301 - 500 lbs', '501 - 1400 lbs'
                ]
            ],
            'industry' => [
                'label' => 'Industry',
                'type' => 'text',
                'input' => 'multiselect',
                'backend' => ArrayBackend::class,
                'options' => [
                    'CH', 'IE'
                ]
            ],
            'market' => [
                'label' => 'Market',
                'type' => 'text',
                'input' => 'multiselect',
                'backend' => ArrayBackend::class,
                'visible_on_front' => false,
                'options' => [
                    'Access Control System',     'Aerospace/Watercraft',
                    'Appliances',                'Architectural & Design',
                    'Automation/Motion Control', 'Automotive',
                    'Transportation',            'Emergency Vehicles',
                    'Homeowners',                'Industrial',
                    'Machinery',                 'Medical',
                    'Electronic Enclosures',     'Truck Bodies',
                    'Utility Vehicles',          'Vending Machine',
                    'Cash Handling',             'Wood Cabinetry'
                ]
            ],
            'application' => [
                'label' => 'Application',
                'type' => 'text',
                'input' => 'multiselect',
                'backend' => ArrayBackend::class,
                'options' => [
                    'Tool Boxes', 'Atm Machines', 'Electronic Enclosures',
                    'Cabinetry',  'Automobiles',  'Emergency Vehicles',
                    'Amoirs',     'TBD'
                ]
            ],
            'optional_kits' => [
                'label' => 'Optional Kits',
                'type' => 'int',
                'input' => 'select',
                'options' => [
                    '4180-0535', '4180-0555'
                ]
            ],
            'rohs' => [
                'label' => 'ROHS',
                'type' => 'int',
                'input' => 'boolean'
            ],
            'bhma' => [
                'label' => 'BHMA',
                'type' => 'int',
                'input' => 'boolean'
            ],
            'awi' => [
                'label' => 'AWI',
                'type' => 'int',
                'input' => 'boolean'
            ],
            'product_for_sales' => [
                'label' => 'Product For Sales',
                'type' => 'int',
                'input' => 'boolean',
                'used_in_product_listing' => true,
                'used_for_sort_by' => true
            ],
            'warranty' => [
                'label' => 'Warranty',
                'type' => 'text',
                'input' => 'textarea',
                'searchable' => false,
                'visible_in_advanced_search' => false,
                'comparable' => false,
                'filterable' => false,
                'filterable_in_search' => false,
                'used_for_promo_rules' => false,
                'visible_on_front' => false
            ],
            'mounting' => [
                'label' => 'Mounting',
                'type' => 'text',
                'input' => 'multiselect',
                'backend' => ArrayBackend::class,
                'visible_on_front' => false,
                'options' => [
                    'Flat Mount', 'Side Mount',     'Undermount',
                    'Overhead',   'Vertical Mount',
                ]
            ],
            'weather_resistant' => [
                'label' => 'Weather Resistant',
                'type' => 'int',
                'input' => 'boolean'
            ],
            'corrosion_resistant' => [
                'label' => 'Corrosion Resistant',
                'type' => 'int',
                'input' => 'boolean'
            ],
            'material' => [
                'label' => 'Material',
                'type' => 'int',
                'input' => 'select',
                'visible_on_front' => false,
                'options' => [
                    'Stainless Steel', 'Aluminum', 'Cold Rolled Steel'
                ]
            ],
            'ball_bearings' => [
                'label' => 'Ball Bearings',
                'type' => 'int',
                'input' => 'select',
                'options' => [
                    'Steel', 'Polymer'
                ]
            ],
            'carraige_options' => [
                'label' => 'Carraige Options',
                'type' => 'int',
                'input' => 'select',
                'options' => [
                    'Non', 'Adjust Manual', 'Auto'
                ]
            ],
            'disconnect' => [
                'label' => 'Disconnect',
                'type' => 'int',
                'input' => 'select',
                'visible_on_front' => false,
                'options' => [
                    'Friction', 'Rail', 'Latch', 'Lever', 'Non-Disconnect'
                ]
            ],
            'bbu' => [
                'label' => 'BBU',
                'type' => 'varchar',
                'input' => 'text'
            ],
            'company_number' => [
                'label' => 'Company Number',
                'type' => 'int',
                'input' => 'select',
                'options' => [
                    '116'
                ]
            ],
            'account_number' => [
                'label' => 'Account Number',
                'type' => 'int',
                'input' => 'select',
                'options' => [
                    '30300', '30400', '30405'
                ]
            ],
            'department' => [
                'label' => 'Department',
                'type' => 'int',
                'input' => 'select',
                'options' => [
                    '757'
                ]
            ],
            'subaccount' => [
                'label' => 'Subaccount',
                'type' => 'int',
                'input' => 'select',
                'options' => [
                    '000000'
                ]
            ],
            'project_number' => [
                'label' => 'Project Number',
                'type' => 'int',
                'input' => 'select',
                'options' => [
                    '0000'
                ]
            ],
            'barcode' => [
                'label' => 'Barcode',
                'type' => 'varchar',
                'input' => 'text'
            ],
            'handed' => [
                'label' => 'Handed',
                'type' => 'int',
                'input' => 'select',
                'visible_on_front' => false,
                'options' => [
                    'Left', 'Right'
                ]
            ],
            'custom_category' => [
                'label' => 'Category',
                'type' => 'int',
                'input' => 'select',
                'visible_on_front' => false,
                'options' => [
                    'Light Duty 139 lbs or less',     'Medium Duty 140 to 169 lbs',
                    'Heavy Duty 170 lbs to 1323 lbs', 'Pocket & Bayonet',
                    'Corrosion Resistant',            'Flipper Door Slides',
                    'Specialty Slides',               'Electronic Enclosure Slides',
                    'Accessories',                    'Cable Carriers',
                    'OEM',                            'Access Control Slides'
                ]
            ],
            'slide_series' => [
                'label' => 'Slide Series',
                'type' => 'int',
                'input' => 'select',
                'visible_on_front' => false,
                'options' => [
                    '1100', '1170', '1200', '1250', '1400', '1405', '2100',
                    '2600', '2700', '2710', '2900', '3000', '3050', '3100',
                    '3600', '3700', '3800', '3826', '4170', '4520', '6300',
                    '7000', '7050', '7900', '9300'
                ]
            ],
            'cost_center' => [
                'label' => 'Cost Center',
                'type' => 'int',
                'input' => 'select',
                'visible_on_front' => false,
                'options' => [
                    '050', '054', '055', '056', '060', '061', '063', '065',
                    '070', '075', '076', '077', '078', '079', '080', '083',
                    '084', '085', '087', '088', '089', '090', '091', '092',
                    '093', '094', '095', '096', '097', '098', '099'
                ]
            ],
            'type' => [
                'label' => 'Type',
                'type' => 'int',
                'input' => 'select',
                'visible_on_front' => false,
                'options' => [
                    'Slide', 'Bracket', 'Kit'
                ]
            ],
            'is_top_choice' => [
                'label' => 'Top Choice',
                'type' => 'int',
                'input' => 'boolean',
                'source' => Boolean::class,
                'default' => Boolean::VALUE_NO,
                'default_value' => Boolean::VALUE_NO
            ],
            'uom' => [
                'label' => 'UOM',
                'type' => 'int',
                'input' => 'select',
                'options' => [
                    'Each', 'Pairs'
                ]
            ],
        ];

        foreach ($additionalAttributes as $code => $fieldInfo) {
            if ($eavSetup->getAttribute(Product::ENTITY, $code, 'attribute_code')) {
                $eavSetup->removeAttribute(Product::ENTITY, $code);
            }
            
            $attributeData = [
                'global'                     => 1,
                'visible'                    => true,
                'required'                   => false,
                'user_defined'               => true,
                'searchable'                 => true,
                'visible_in_advanced_search' => true,
                'filterable'                 => true,
                'filterable_in_search'       => true,
                'comparable'                 => true,
                'used_for_promo_rules'       => true,
                'html_allowed_on_front'      => false,
                'visible_on_front'           => true,
                'used_in_product_listing'    => false,
                'used_for_sort_by'           => false,
                'system'                     => 1,
                'group'                      => $groupName,
                'apply_to'                   => ''
            ];
            
            foreach ($fieldInfo as $field => $value) {
                if ($field == 'options') {
                    $attributeData['option']['values'] = $value;
                } else {
                    $attributeData[$field] = $value;
                }
            }
            
            $eavSetup->addAttribute(
                Product::ENTITY,
                $code,
                $attributeData
            );
        }
    }
    
    /**
     * Update several attributes
     *
     * @param EavSetup $eavSetup
     */
    protected function updateSeveralAttributes(EavSetup $eavSetup)
    {
        $finishUpdate = [
            [
                'backend_type',
                'text'
            ],
            [
                'frontend_input',
                'multiselect'
            ],
            [
                'backend_model',
                ArrayBackend::class
            ]
        ];
        
        foreach ($finishUpdate as $config) {
            $eavSetup->updateAttribute(Product::ENTITY, 'finish', $config[0], $config[1]);
        }
        
        $options = [];
        $optionsToAdd = ['071'];
        $costCenterId = $eavSetup->getAttributeId(Product::ENTITY, 'cost_center');
        if ($costCenterId) {
            $options['attribute_id'] = $costCenterId;
            foreach ($optionsToAdd as $option) {
                $options['values'][] = $option;
            }
        }
        $eavSetup->addAttributeOption($options);
    }
    
    /**
     * Update several attributes
     *
     * @param EavSetup $eavSetup
     */
    protected function updateLengthAndSideSpaceAttributes(EavSetup $eavSetup)
    {
        $options = [];
        $optionsToAdd = [
            '38"', '39.37"', '54"', '78.74"'
        ];
        $lengthId = $eavSetup->getAttributeId(Product::ENTITY, 'length');
        if ($lengthId) {
            $options['attribute_id'] = $lengthId;
            foreach ($optionsToAdd as $option) {
                $options['values'][] = $option;
            }
        }
        $eavSetup->addAttributeOption($options);
        
        $groupName = 'Accuride attributes';
        $sideSpaceCode = 'side_space';
        
        if ($eavSetup->getAttribute(Product::ENTITY, $sideSpaceCode, 'attribute_code')) {
            $eavSetup->removeAttribute(Product::ENTITY, $sideSpaceCode);
        }
        
        $attributeData = [
            'label'                      => 'Side Space',
            'type'                       => 'int',
            'input'                      => 'select',
            'global'                     => 1,
            'visible'                    => true,
            'required'                   => false,
            'user_defined'               => true,
            'searchable'                 => true,
            'visible_in_advanced_search' => true,
            'filterable'                 => true,
            'filterable_in_search'       => true,
            'comparable'                 => true,
            'used_for_promo_rules'       => true,
            'html_allowed_on_front'      => false,
            'visible_on_front'           => false,
            'used_in_product_listing'    => false,
            'used_for_sort_by'           => false,
            'system'                     => 1,
            'group'                      => $groupName,
            'apply_to'                   => '',
            'option'                     => [
                'values' => [
                    'Less than .50"',        '.50"',
                    'Between .50" and .75"', '.75"',
                    'More than .75"'
                ]
            ]
        ];

        $eavSetup->addAttribute(
            Product::ENTITY,
            $sideSpaceCode,
            $attributeData
        );
    }

    /**
     * Get Applications attribute
     * @return array
     */
    protected function getApplicationsAttribute()
    {
        return [
            'type' => 'int',
            'label' => 'Applications',
            'input' => 'select',
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => true,
            'used_in_product_listing' => false,
            'unique' => false,
            'group' => 'Attributes',
            'apply_to' => 'simple,configurable',
            'sort_order' => 10,
            'default' => ''
        ];
    }

    /**
     * Add Applications attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    protected function addApplicationsAttribute(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            Product::ENTITY,
            'applications',
            $this->getApplicationsAttribute()
        );
    }
    
    /**
     * Update applications attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    protected function updateApplicationsAttribute(EavSetup $eavSetup)
    {
        $code = 'applications';
        
        if ($eavSetup->getAttribute(Product::ENTITY, $code, 'attribute_code')) {
            $eavSetup->removeAttribute(Product::ENTITY, $code);
        }

        $attributeData = [
            'label'                      => 'Applications',
            'type'                       => 'text',
            'input'                      => 'textarea',
            'global'                     => 1,
            'visible'                    => true,
            'required'                   => false,
            'user_defined'               => true,
            'searchable'                 => false,
            'filterable'                 => false,
            'comparable'                 => false,
            'html_allowed_on_front'      => true,
            'visible_on_front'           => false,
            'used_in_product_listing'    => false,
            'used_for_sort_by'           => false,
            'system'                     => 1,
            'group'                      => 'Attributes',
            'apply_to'                   => '',
            'sort_order'                 => 10
        ];

        $eavSetup->addAttribute(
            Product::ENTITY,
            $code,
            $attributeData
        );
    }
    
    /**
     * Recreate slide selector multiselect attributes
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    protected function recreateSlideSelectorAttributes(EavSetup $eavSetup)
    {
        $additionalAttributes = [
            'special_features' => [
                'label' => 'Special Features',
                'type' => 'varchar',
                'input' => 'multiselect',
                'visible_on_front' => false,
                'backend' => ArrayBackend::class,
                'options' => [
                    'Corrision-Resistance',  'Detent-In',      'Detent-Out',
                    'Easy Close/Soft Close', 'Easy Down',      'Lock-In',
                    'Lock-Out',              'Locking Handle', 'Overhead',
                    'Self-Close',            'Touch Release',  'Two Way Travel',
                    'Interlock'
                ]
            ],
            'finish' => [
                'label' => 'Finish',
                'type' => 'varchar',
                'input' => 'multiselect',
                'backend' => ArrayBackend::class,
                'options' => [
                    'Zinc', 'White', 'Black', 'Ch'
                ]
            ],
            'market' => [
                'label' => 'Market',
                'visible_on_front' => false,
                'options' => [
                    'Access Control System',     'Aerospace/Watercraft',
                    'Appliances',                'Architectural & Design',
                    'Automation/Motion Control', 'Automotive',
                    'Transportation',            'Emergency Vehicles',
                    'Homeowners',                'Industrial',
                    'Machinery',                 'Medical',
                    'Electronic Enclosures',     'Truck Bodies',
                    'Utility Vehicles',          'Vending Machine',
                    'Cash Handling',             'Wood Cabinetry'
                ]
            ],
            'mounting' => [
                'label' => 'Mounting',
                'type' => 'varchar',
                'input' => 'multiselect',
                'backend' => ArrayBackend::class,
                'visible_on_front' => false,
                'options' => [
                    'Flat Mount', 'Side Mount',     'Undermount',
                    'Overhead',   'Vertical Mount',
                ]
            ]
        ];

        foreach ($additionalAttributes as $code => $fieldInfo) {
            if ($eavSetup->getAttribute(Product::ENTITY, $code, 'attribute_code')) {
                $eavSetup->removeAttribute(Product::ENTITY, $code);
            }
            
            $attributeData = [
                'type'                       => 'varchar',
                'input'                      => 'multiselect',
                'backend'                    => ArrayBackend::class,
                'global'                     => 1,
                'visible'                    => true,
                'required'                   => false,
                'user_defined'               => true,
                'searchable'                 => true,
                'visible_in_advanced_search' => true,
                'filterable'                 => true,
                'filterable_in_search'       => true,
                'comparable'                 => true,
                'used_for_promo_rules'       => true,
                'html_allowed_on_front'      => false,
                'visible_on_front'           => true,
                'used_in_product_listing'    => false,
                'used_for_sort_by'           => false,
                'system'                     => 1,
                'group'                      => 'Accuride attributes',
                'apply_to'                   => ''
            ];
            
            foreach ($fieldInfo as $field => $value) {
                if ($field == 'options') {
                    $attributeData['option']['values'] = $value;
                } else {
                    $attributeData[$field] = $value;
                }
            }
            
            $eavSetup->addAttribute(
                Product::ENTITY,
                $code,
                $attributeData
            );
        }
    }
    
    /**
     * Update product features attribute
     *
     * @param EavSetup $eavSetup
     */
    protected function updateProductFeaturesAttribute(EavSetup $eavSetup)
    {
        $productFeaturesCode = 'product_features';
        
        if ($eavSetup->getAttribute(Product::ENTITY, $productFeaturesCode, 'attribute_code')) {
            $eavSetup->removeAttribute(Product::ENTITY, $productFeaturesCode);
        }

        $attributeData = [
            'label'                      => 'Product Features',
            'type'                       => 'text',
            'input'                      => 'textarea',
            'global'                     => 1,
            'visible'                    => true,
            'required'                   => false,
            'user_defined'               => true,
            'searchable'                 => false,
            'filterable'                 => false,
            'comparable'                 => false,
            'html_allowed_on_front'      => true,
            'visible_on_front'           => false,
            'used_in_product_listing'    => false,
            'used_for_sort_by'           => false,
            'system'                     => 1,
            'group'                      => 'Accuride attributes',
            'apply_to'                   => ''
        ];

        $eavSetup->addAttribute(
            Product::ENTITY,
            $productFeaturesCode,
            $attributeData
        );
    }
    
    /**
     * Add attribute options
     *
     * @param array $optionsToAdd
     * @param string $code
     * @param EavSetup $eavSetup
     * @return void
     */
    protected function addAttributeOptions(array $optionsToAdd, $code, EavSetup $eavSetup)
    {
        $options = [];
        
        $attrId = $eavSetup->getAttributeId(Product::ENTITY, $code);
        if ($attrId) {
            $options['attribute_id'] = $attrId;
            foreach ($optionsToAdd as $option) {
                $options['values'][] = $option;
            }
        }
        
        $eavSetup->addAttributeOption($options);
    }

    /**
     * Hide product attributes on front
     *
     * @param EavSetup $eavSetup
     */
    protected function hideProductAttributes(EavSetup $eavSetup)
    {
        $attrsToUpdate = [
            'bbu',           'company_number',   'account_number',
            'subaccount',    'project_number',   'barcode',
            'slide_series',  'cost_center',      'uom',
            'optional_kits', 'carraige_options'
        ];
        
        $data = [
            'is_searchable' => false,
            'is_visible_in_advanced_search' => false,
            'is_comparable' => false,
            'is_filterable' => false,
            'is_filterable_in_search' => false,
            'is_used_for_promo_rules' => false,
            'is_visible_on_front' => false
        ];
        
        foreach ($attrsToUpdate as $attribute) {
            foreach ($data as $config => $value) {
                $eavSetup->updateAttribute(Product::ENTITY, $attribute, $config, $value);
            }
        }
        
        $eavSetup->updateAttribute(Product::ENTITY, 'barcode', 'frontend_label', 'Barcode');
        $eavSetup->updateAttribute(Product::ENTITY, 'carraige_options', 'frontend_label', 'Carriage Options');
        
        $optionsToAdd = ['Automation & Control'];
        $this->addAttributeOptions($optionsToAdd, 'market', $eavSetup);
    }
    
    /**
     * Update top choice attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    protected function updateTopChoiceAttribute(EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(Product::ENTITY, 'is_top_choice', 'used_in_product_listing', true);
    }
    
    /**
     * Update uom attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    protected function updateUomAttribute(EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(Product::ENTITY, 'uom', 'frontend_label', 'Quantity In');
    }
}
