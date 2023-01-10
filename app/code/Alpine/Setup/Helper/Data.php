<?php
/**
 * Alpine_Setup
 *
 * @category    Alpine
 * @package     Alpine_Setup
 * @copyright   Copyright (c) 2017 Alpine Consulting, Inc (www.alpineinc.com)
 * @author      jjosephson@alpineinc.com
 */

namespace Alpine\Setup\Helper;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;


    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    private $eavSetup;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $setFactory;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /** @var \Magento\Catalog\Model\CategoryFactory */
    protected $categoryFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\Framework\App\ResourceConnection  */
    protected $resourceConnection;
    /**
     * Init
     *
     * @param BlockFactory $modelBlockFactory
     * @param PageFactory $modelPageFactory
     */
    public function __construct(
        BlockFactory $modelBlockFactory,
        PageFactory $modelPageFactory,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->blockFactory = $modelBlockFactory;
        $this->pageFactory = $modelPageFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->categoryFactory = $categoryFactory;
        $this->setFactory = $setFactory;
        $this->moduleList = $moduleList;
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;

        $this->eavSetup = $this->eavSetupFactory->create();
    }

    /**
     * @param array $cmsBlock
     * Example
     * $cmsBlocks = [
     * [
     * 'title' => 'Order confirmation info text',
     * 'identifier' => 'order_confirmation_text',
     * 'content' => '',
     * 'is_active' => 1,
     * 'stores' => [1],
     * 'overwrite' => 0,
     * ]
     * ];
     * $this->createBlockFromArray($cmsBlocks);
     *
     * @return bool
     */

    public function createBlockFromArray($cmsBlock = array())
    {
        foreach ($cmsBlock as $data) {

            $block = $this->blockFactory->create()->load($data['identifier'], 'identifier');

            if ($block->getId() && (!isset($data['overwrite']) || $data['overwrite'] != 1)) {
                continue;
            }

            if (!$block->getId()) {
                $block->setIdentifier($data['identifier']);
            }
            $identifier = $block->getIdentifier();
            if (!isset($identifier) || empty($identifier)) {
                $block->setIdentifier($data['identifier']);
            }
            $block->setContent($data['content']);
            $block->setTitle($data['title']);
            $block->setIsActive($data['is_active']);
            $block->setStores($data['stores']);
            $block->save();
        }
        return true;
    }

    /**
     * @param array $cmsPages
     *
     *  sample
     * $cmsPages = [
     * [
     * 'title' => 'Rubies - Test Page',
     * 'identifier' => 'test_page',
     * 'content' => '
     * {{block id="home-carousel"}}
     * {{block id="home-bestsellers"}}
     * {{block id="home-reviews"}}
     * ',
     * 'content_heading' => '',
     * 'meta_keywords' => '',
     * 'meta_description' => '',
     * 'is_active' => 1,
     * 'page_layout' => '1column',
     * 'stores' => array(2),
     * 'sort_order' => 0,
     * 'overwrite' => 0,
     * ],
     * ]
     * $this->createPageFromArray($cmsPages);
     *     * @return bool
     */
    public function createPageFromArray($cmsPages = array())
    {
        foreach ($cmsPages as $data) {

            $page = $this->pageFactory->create()->load($data['identifier'], 'identifier');

            if ($page->getId() && (!isset($data['overwrite']) || $data['overwrite'] != 1)) {
                continue;
            }

            if (!$page->getId()) {
                $page->setIdentifier($data['identifier']);
            }

            $page->setContent($data['content']);
            $page->setContentHeading($data['content_heading']);
            $page->setMetaKeywords($data['meta_keywords']);
            $page->setMetaDescription($data['meta_description']);
            $page->setTitle($data['title']);
            $page->setIsActive($data['is_active']);
            $page->setPageLayout($data['page_layout']);
            $page->setSortOrder($data['sort_order']);
            $page->setStores($data['stores']);
            $page->save();
        }
        return true;
    }

    public function getUpdatesFileList($baseDir, $context, $moduleName)
    {
        // context version = db version
        // configVer = module.xml version
        // fileVersion = file version
        $module = $this->moduleList->getOne($moduleName);
        $configVer = $module['setup_version'];

        $updates = $baseDir . '/*.php';
//        $this->log('Base Updates Dir: ' . $updates);
        $files = glob($updates);
        usort($files, 'version_compare');
        $upgradeFiles = [];
        foreach ($files as $file) {
//            $this->log('Found File: ' . $file);
            $baseName = basename($file);
            if (preg_match('/\d*\.\d*\.\d*\.php/', $baseName) !== 1) continue;

            $fileVersion = preg_replace('/\.php/', '', $baseName);
//            $this->log($version);
            // for installation ->getVersion() = ''
            // EMPTY = installing
            //
            if ($context->getVersion() && version_compare($context->getVersion(), $fileVersion) < 0) {
                $upgradeFiles[] = $file;
                // empty if we are installing
            } else if (empty($context->getVersion()) && version_compare($fileVersion, $configVer) <= 0) {
                $upgradeFiles[] = $file;
            }
        }
//        $this->log('');
        return $upgradeFiles;
    }

    public function copyDefaultAttributeSet($newAttrSetName, $entityTypeName = \Magento\Catalog\Model\Product::ENTITY)
    {
        // based on Magento\Catalog\Controller\Adminhtml\Product\Set\Save logic
        $attributeSet = $this->setFactory->create();
        $entityTypeId = $this->eavSetup->getEntityTypeId($entityTypeName);
        $attributeSet->setAttributeSetName($newAttrSetName);
        $attributeSet->setEntityTypeId($entityTypeId);
        $attributeSet->validate();
        $attributeSet->save();
        $defaultSetId = $this->eavSetup->getDefaultAttributeSetId($entityTypeId);
        $attributeSet->initFromSkeleton($defaultSetId);
        $attributeSet->save();
    }

    public function addAttributes($attributes, $entityType = \Magento\Catalog\Model\Product::ENTITY)
    {
        foreach ($attributes as $code => $attribute) {
            $data = isset($attribute['data']) ? $attribute['data'] : $attributes;
            $set = isset($attribute['set']) ? $attribute['set'] : 'Default';
            $group = isset($attribute['group']) ? $attribute['group'] : 'General';
            $sortOrder = isset($attribute['sort_order']) ? $attribute['sort_order'] : NULL;
            $remove = isset($attribute['remove']) ? $attribute['remove'] : FALSE;

            if ($remove) {
                $this->eavSetup->removeAttribute($entityType, $code);
            }
            if (!empty($data)) {
                $this->eavSetup->addAttribute(
                    $entityType,
                    $code,
                    $data
                );
            }

            // accepts strings for entityType, set, group, and attributeId (code)
            $this->eavSetup->addAttributeToSet($entityType, $set, $group, $code, $sortOrder);
        }
    }

    /**
     * @param \Magento\Catalog\Model\Category $parentCategory
     * @param array $categories
     */
    public function createCategoriesFromArray($storeId, $parentCategory, $categories)
    {
        if (empty($parentCategory)) {
            $parentCategory = $this->loadRootCategory($storeId);
        }
        $categories = is_array($categories) ? $categories : array($categories);
        foreach ($categories as $categoryData) {
//            $this->log(print_r($categoryData, TRUE));
            $name = $categoryData['name'];
            if (isset($categoryData['url'])) {
                $url = $categoryData['url'];
            } else {
                $url = strtolower($name);
                $url = trim(preg_replace('/ +/', '-', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($url))))));
                $url = preg_replace('/-+/', '-', $url);
            }
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->categoryFactory->create();
            $category->setName($name);
            $category->setIsActive(true);
            $category->setUrlKey($url);
            $category->setParentId($parentCategory->getId());
            $category->setStoreId($storeId);
            $category->setPath($parentCategory->getPath());
            $category->save();
            if (isset($categoryData['children'])) {
//                foreach ($categoryData['children'] as $childCategory) {
                $this->createCategoriesFromArray($storeId, $category, $categoryData['children']);
//                }
            }
        }
    }

    public function loadRootCategory($storeId)
    {
        $store = $this->storeManager->getStore($storeId);
        $rootCategoryId = $store->getRootCategoryId();
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->categoryFactory->create();
        $category->load($rootCategoryId);
        return $category;
    }

    public function importCustomOptions($attributeCode, $options, $type = 'dropdown')
    {
        $attributeData = $this->eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        $this->deleteAttributeOptions($attributeData['attribute_id']); //optional
        $options = $this->generateOptions($options, $type);
        foreach ($options as $option) {
            $option['attribute_id'] = $attributeData['attribute_id'];
            $this->eavSetup->addAttributeOption($option);
        }
        return $this;
    }

    public function deleteAttributeOptions($attribute_id)
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('eav_attribute_option');
        $sql = "DELETE FROM " . $table . " WHERE attribute_id = " . $attribute_id;
        $connection->query($sql);

        $this->log("Deleted existing options for attribute {$attribute_id}");
        return true;
    }

    protected function generateOptions(array $values, $optionType = 'dropdown')
    {
        $i = 0;
        foreach ($values as $value) {
            $order["option_{$i}"] = $i;
            $optionsStore["option_{$i}"] = array(
                0 => $value, // admin
                1 => $value, // default store view
            );
            $textSwatch["option_{$i}"] = array(
                1 => $value,
            );
            $visualSwatch["option_{$i}"] = '';
            $delete["option_{$i}"] = '';
            $this->log(" - Option {$value} added for the attribute.");
            $i++;
        }

        switch ($optionType) {
            case 'text':
                return [
                    'optiontext' => [
                        'order' => $order,
                        'value' => $optionsStore,
                        'delete' => $delete,
                    ],
                    'swatchtext' => [
                        'value' => $textSwatch,
                    ],
                ];
                break;
            case 'visual':
                return [
                    'optionvisual' => [
                        'order' => $order,
                        'value' => $optionsStore,
                        'delete' => $delete,
                    ],
                    'swatchvisual' => [
                        'value' => $visualSwatch,
                    ],
                ];
                break;
            default:
                return [
                    'option' => [
                        'order' => $order,
                        'value' => $optionsStore,
                        'delete' => $delete,
                    ],
                ];
        }
    }

    /**
     * @return \Magento\Eav\Setup\EavSetup
     */
    public function getEavSetup()
    {
        return $this->eavSetup;
    }

    /**
     * @return \Magento\Eav\Model\AttributeRepository
     */
    public function getAttributeRepository()
    {
        return $this->attributeRepository;
    }

    public function log($message)
    {
        echo PHP_EOL . $message;
    }
}
