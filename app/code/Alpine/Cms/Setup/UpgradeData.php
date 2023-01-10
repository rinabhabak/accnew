<?php
/**
 * Alpine_Cms
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Denis Furman <denis.furman@alpineinc.com>
 * @author      Aleksandr Mikhailov <aleksandr.mikhailov@alpineinc.com>
 * @author      ilya.antonov@alpineinc.com
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */

namespace Alpine\Cms\Setup;

use Alpine\Cms\Model\CmsSetup;
use Magento\Cms\Block\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\App\State;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config as Config;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use VladimirPopov\WebForms\Model\FormFactory;
use VladimirPopov\WebForms\Model\ResourceModel\FormFactory as FormResourceFactory;

/**
 * Alpine\Cms\Setup\UpgradeData
 *
 * @category    Alpine
 * @package     Alpine_Cms
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * SetupCms.
     *
     * @var CmsSetup
     */
    protected $setupCMS;
    /**
     * @var ModuleContextInterface
     */
    protected $context;
    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;
    /**
     * Resource Config
     *
     * @var Config
     */
    protected $configFactory;

    /**
     * Category factory
     *
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Form Factory
     *
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * Form Resource Factory
     *
     * @var FormResourceFactory
     */
    protected $formResourceFactory;

    /**
     * App State
     *
     * @var State
     */
    protected $appState;

    /**
     * Block Factory
     *
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * Block Repository
     *
     * @var BlockRepository
     */
    protected $blockRepository;

    /**
     * InstallData constructor.
     *
     * @param CmsSetup                $setupCMS
     * @param PageRepositoryInterface $pageRepository
     * @param Config                  $configFactory
     * @param CategoryFactory         $categoryFactory
     * @param BlockFactory            $blockFactory
     * @param BlockRepository         $blockRepository
     * @param FormFactory             $formFactory
     * @param FormResourceFactory     $formResourceFactory
     * @param State                   $appState
     */
    public function __construct(
        CmsSetup $setupCMS,
        PageRepositoryInterface $pageRepository,
        Config $configFactory,
        CategoryFactory $categoryFactory,
        BlockFactory $blockFactory,
        BlockRepository $blockRepository,
        FormFactory $formFactory,
        FormResourceFactory $formResourceFactory,
        State $appState
    ) {
        $this->setupCMS            = $setupCMS;
        $this->pageRepository      = $pageRepository;
        $this->configFactory       = $configFactory;
        $this->categoryFactory     = $categoryFactory;
        $this->formFactory         = $formFactory;
        $this->formResourceFactory = $formResourceFactory;
        $this->appState            = $appState;
        $this->blockFactory        = $blockFactory;
        $this->blockRepository     = $blockRepository;
    }

    /**
     * Upgrade data
     *
     * @param  ModuleDataSetupInterface $setup
     * @param  ModuleContextInterface   $context
     *
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->context = $context;
        //Install cms block
        $this->upgradeToVersion('0.0.2');
        //Add cms block on home page
        $this->upgradeToVersion('0.0.3');
        //Add cms page
        $this->upgradeToVersion('0.0.4');

        if (version_compare($this->context->getVersion(), '0.0.5', '<')) {
            $this->createPagesForPopups();
        }
        //Add cms page
        $this->upgradeToVersion('0.0.6');
        //Add cms page
        $this->upgradeToVersion('0.0.7');

        //Update content cms page
        $this->upgradeToVersion('0.0.8');

        if (version_compare($this->context->getVersion(), '0.1.0', '<')) {
            $this->setupCMS->loadAndInstallBlock('Alpine_Cms::Setup/include/slider_selector_home.csv');
        }
        if (version_compare($this->context->getVersion(), '0.4.0', '<')) {
            $this->setupCMS->loadAndInstallBlock('Alpine_Cms::Setup/include/cms_block_product.csv');
        }

        //Add cms page
        $this->upgradeToVersion('0.4.1');

        if (version_compare($this->context->getVersion(), '0.4.2', '<')) {
            $this->setupCMS->loadAndInstallBlock('Alpine_Cms::Setup/include/cms_block_warranty.csv');
            $this->setupCMS->loadAndInstallBlock('Alpine_Cms::Setup/include/cms_block_support.csv');
            $this->setupCMS->loadAndInstallBlock('Alpine_Cms::Setup/include/cms_block_shipping.csv');

            $cmsPages = [
                'Alpine_Cms::Setup/include/warranty.csv',
                'Alpine_Cms::Setup/include/support.csv',
                'Alpine_Cms::Setup/include/shipping.csv'
            ];
            foreach ($cmsPages as $pageId) {
                $this->setupCMS->loadPages($pageId);
            }
        }

        if (version_compare($this->context->getVersion(), '0.4.3', '<')) {
            $this->setupCMS->loadAndInstallBlock('Alpine_Cms::Setup/include/cms_block_support.csv');
        }

        if (version_compare($this->context->getVersion(), '0.4.4', '<')) {
            $cmsPages = [
                'Alpine_Cms::Setup/include/warranty.csv',
                'Alpine_Cms::Setup/include/support.csv',
                'Alpine_Cms::Setup/include/shipping.csv'
            ];
            foreach ($cmsPages as $pageId) {
                $this->setupCMS->loadPages($pageId);
            }
        }

        //Update content cms page
        if (version_compare($this->context->getVersion(), '0.4.5', '<')) {
            $cmsPage = [
                'Alpine_Cms::Setup/include/drawer.csv'
            ];

            foreach ($cmsPage as $blockId) {
                $this->setupCMS->loadPages($blockId);
            }
        }


        if (version_compare($this->context->getVersion(), '0.4.6', '<')) {
            $this->configFactory->saveConfig('general/store_information/phone', '562.903.0200',ScopeConfigInterface::SCOPE_TYPE_DEFAULT, Store::DEFAULT_STORE_ID);
        }

        //Upgrade cms block on home page
        if (version_compare($this->context->getVersion(), '0.4.7', '<')) {
            $cmsBlocks = [
                'Alpine_Cms::Setup/include/home_cms_block.csv'
            ];

            foreach ($cmsBlocks as $blockId) {
                $this->setupCMS->loadAndInstallBlock($blockId);
            }
        }

        if (version_compare($this->context->getVersion(), '0.4.8', '<')) {
            $cmsPage = [
                'Alpine_Cms::Setup/include/privacy.csv'
            ];

            foreach ($cmsPage as $blockId) {
                $this->setupCMS->loadPages($blockId);
            }
        }

        if (version_compare($this->context->getVersion(), '0.5.0', '<')) {
            $specialitySlidesCsv = 'Alpine_Cms::Setup/include/specialty_slides.csv';
            $this->setupCMS->loadAndInstallBlock($specialitySlidesCsv);
            $specialtySlidesBlock = $this->setupCMS->getCmsBlockById('specialty_slides');

            if ($specialtySlidesBlock) {
                $category = $this->categoryFactory->create()
                    ->loadByAttribute('name', 'Specialty Slides');

                if ($category->getId()) {
                    $category->setDisplayMode(Category::DM_PAGE);
                    $category->setLandingPage($specialtySlidesBlock->getId());
                    $category->save();
                }
            }

            $specialtySlidesPage = $this->setupCMS->getCmsPageById('specialty-slides');
            if ($specialtySlidesPage) {
                $specialtySlidesPage->setIsActive(false);
                $specialtySlidesPage->save();
            }
        }

        if (version_compare($this->context->getVersion(), '0.5.2', '<')) {

            $this->setupCMS->loadAndInstallBlock('Alpine_Cms::Setup/include/error_page_blocks.csv');

            $content = '';
            $blocks  = [
                'error_page_main',
                'error_page_links'
            ];
            foreach ($blocks as $item) {
                $block = $this->setupCMS->getCmsBlockById($item);
                if ($block && $block->getId()) {
                    $content .= '<p>{{widget type="Magento\Cms\Block\Widget\Block" template="widget/static_block/default.phtml" block_id="' . $block->getId() . '"}}</p>';
                }
            }
            $cmsPage = $this->pageRepository->getById('no-route');
            if ($cmsPage && $cmsPage->getId()) {
                $cmsPage->setContentHeading('')
                    ->setPageLayout('1column')
                    ->setContent($content);
                $this->pageRepository->save($cmsPage);
            }
        }

        if (version_compare($this->context->getVersion(), '0.5.2', '<')) {
            $this->setupCMS->loadAndInstallBlock('Alpine_Cms::Setup/include/error_page_blocks.csv');
        }

        //Add cms block for header top links
        if (version_compare($this->context->getVersion(), '0.5.3', '<')) {
            $this->setupCMS->loadAndInstallBlock('Alpine_Cms::Setup/include/cms_block_header_top_links.csv');
        }

        if (version_compare($this->context->getVersion(), '0.5.4', '<')) {
            $this->setupCMS->loadAndInstallBlock('Alpine_Cms::Setup/include/error_page_blocks.csv');

            $content = '';
            $blocks  = [
                'error_page_main',
                'error_page_links'
            ];
            foreach ($blocks as $item) {
                $block = $this->setupCMS->getCmsBlockById($item);
                if ($block && $block->getId()) {
                    $content .= '<p>{{widget type="Magento\Cms\Block\Widget\Block" template="widget/static_block/default.phtml" block_id="' . $block->getId() . '"}}</p>';
                }
            }
            $content .= '{{widget type="VladimirPopov\WebForms\Block\Widget\Form" webform_id="13"
                template="VladimirPopov_WebForms::webforms/form/default.phtml" after_submission_form="0" scroll_to="0"}}';

            $cmsPage = $this->pageRepository->getById('no-route');
            if ($cmsPage && $cmsPage->getId()) {
                $cmsPage->setContentHeading('')
                    ->setPageLayout('1column')
                    ->setContent($content);
                $this->pageRepository->save($cmsPage);
            }
        }

        if (version_compare($this->context->getVersion(), '0.6.0', '<')) {
            $this->updateCollectionProductsHomeBlock();
        }

        if (version_compare($this->context->getVersion(), '0.7.0', '<')) {
            $this->appState->emulateAreaCode('adminhtml', [$this, 'updateContentOfEACSupportForm']);
        }

        if (version_compare($this->context->getVersion(), '0.7.1', '<')) {
            $this->setupCMS->loadAndInstallBlock('Alpine_Cms::Setup/include/cms_block_support.csv');
        }

        $setup->endSetup();
    }

    /**
     * Runs upgrade to version number method
     *
     * @param $versionNumber
     */
    protected function upgradeToVersion($versionNumber)
    {
        if (version_compare($this->context->getVersion(), $versionNumber, '<')) {
            $methodName = 'upgradeTo' . str_replace('.', '_', $versionNumber);
            $this->$methodName();
        }
    }

    /**
     * Install cms block's
     *
     * @throws \Exception
     */
    protected function upgradeTo0_0_2()
    {
        $cmsBlocks = [
            'Alpine_Cms::Setup/include/home_cms_block.csv'
        ];

        foreach ($cmsBlocks as $blockId) {
            $this->setupCMS->loadAndInstallBlock($blockId);
        }
    }

    /**
     * Add cms block on home page
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function upgradeTo0_0_3()
    {
        $content = '';
        $blocks  = [
            'top_slider_home',
            'slider_second_home',
            'slider_third_home',
            'block_fourth_home',
            'slider_selector_home',
            'collection_products_home'
        ];
        foreach ($blocks as $item) {
            $block = $this->setupCMS->getCmsBlockById($item);
            if ($block && $block->getId()) {
                $content .= '<p>{{widget type="Magento\Cms\Block\Widget\Block" template="widget/static_block/default.phtml" block_id="' . $block->getId() . '"}}</p>';
            }
            $cmsPage = $this->pageRepository->getById('home');
            if ($cmsPage && $cmsPage->getId()) {
                $cmsPage->setContentHeading('')
                    ->setContent($content);
                $this->pageRepository->save($cmsPage);
            }
        }
    }

    /**
     * Add cms page
     *
     * @throws \Exception
     */
    protected function upgradeTo0_0_4()
    {
        $cmsPage = [
            'Alpine_Cms::Setup/include/warranty.csv'
        ];

        foreach ($cmsPage as $blockId) {
            $this->setupCMS->loadPages($blockId);
        }
    }

    /**
     * Add cms page
     */
    protected function upgradeTo0_0_6()
    {
        $cmsPage = [
            'Alpine_Cms::Setup/include/cms_page_markets.csv'
        ];

        foreach ($cmsPage as $blockId) {
            $this->setupCMS->loadPages($blockId);
        }
    }

    /**
     * Create CMS Pages for popups in PDP
     *
     * @throws \Exception
     */
    protected function createPagesForPopups()
    {
        $cmsPage = [
            'Alpine_Cms::Setup/include/support.csv',
            'Alpine_Cms::Setup/include/shipping.csv'
        ];

        foreach ($cmsPage as $blockId) {
            $this->setupCMS->loadPages($blockId);
        }
    }

    /**
     * Add cms page
     */
    protected function upgradeTo0_0_7()
    {
        $cmsPage = [
            'Alpine_Cms::Setup/include/drawer.csv'
        ];

        foreach ($cmsPage as $blockId) {
            $this->setupCMS->loadPages($blockId);
        }
    }

    /**
     * Add cms page
     *
     * @throws \Exception
     */
    protected function upgradeTo0_0_8()
    {
        $cmsPage = [
            'Alpine_Cms::Setup/include/warranty.csv'
        ];

        foreach ($cmsPage as $blockId) {
            $this->setupCMS->loadPages($blockId);
        }
    }

    /**
     * Add cms page
     *
     * @throws \Exception
     */
    protected function upgradeTo0_4_1()
    {
        $cmsPage = [
            'Alpine_Cms::Setup/include/privacy.csv'
        ];

        foreach ($cmsPage as $blockId) {
            $this->setupCMS->loadPages($blockId);
        }
    }

    /**
     * Update content of "Collection products home" block
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateCollectionProductsHomeBlock()
    {
        $blockContent = <<<blockContent
<p>{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" title="New Products" show_pager="0" products_count="3" template="product/widget/content/grid.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`()`,`value`:`116RC Heavy-Duty Linear Track System, 115RC Linear Track System, 9301E`^]^]"}}</p>
blockContent;

        $block = $this->blockRepository->getById('collection_products_home');

        if ($block->getId()) {
            $block->setContent($blockContent);
            $this->blockRepository->save($block);
        }
    }

    /**
     * Update description of "EAC Support" web-form
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateContentOfEACSupportForm()
    {
        $form = $this->formFactory->create();

        $formResource = $this->formResourceFactory->create();

        $formResource->load($form, 'eac_support', 'code');

        if ($form->getId()) {
            $formDescription = <<<formDescription
<h2>Integrated Access Control Support</h2>
<p>For inquiries regarding secure access control systems.</p>
formDescription;

            $form->setDescription($formDescription);
            $formResource->save($form);
        }
    }
}
