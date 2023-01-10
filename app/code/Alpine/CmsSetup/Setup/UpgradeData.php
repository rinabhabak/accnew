<?php
/**
 * Alpine_CmsSetup
 *
 * @category    Alpine
 * @package     Alpine_CmsSetup
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc (www.alpineinc.com)
 * @author      jjosephson@alpineinc.com
 * @author      iurii.ziukov@alpineinc.com
 * @author      Denis Furman <denis.furman@alpinainc.com>
 * @author      Valery Shishkin <valery.shishkin@alpineinc.com>
 * @author      Lev Zamansky <lev.zamanskiy@alpineinc.com>
 */

namespace Alpine\CmsSetup\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File as FileReader;
use Magento\Framework\Module\Dir\Reader as DirReader;
use Magento\Framework\Webapi\Exception;
use Magento\Store\Model\Store;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Setup folder name
     */
    const SETUP_FOLDER = 'Setup';

    /**
     * Folder with html files
     */
    const HTML_FOLDER = 'Pages';

    /**
     * html filename suffix
     */
    const HTML_SUFFIX = '.html';

    /**
     * Layout of new created pages
     */
    const NEW_PAGE_LAYOUT = '1column';

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;

    /**
     * @var \Alpine\Setup\Helper\Data
     */
    private $setupHelper;

    /**
     * PageRepository instance
     *
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * FileReader instance
     *
     * @var FileReader
     */
    protected $fileReader;

    /**
     * DirReader instance
     *
     * @var DirReader
     */
    protected $dirReader;

    /**
     * UpgradeData constructor.
     *
     * @param BlockFactory $modelBlockFactory
     * @param PageFactory $modelPageFactory
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param \Alpine\CmsSetup\Helper\Data $setupHelper
     * @param PageRepository $pageRepository
     * @param FileReader $fileReader
     * @param DirReader $dirReader
     */
    public function __construct(
        BlockFactory $modelBlockFactory,
        PageFactory $modelPageFactory,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        \Alpine\CmsSetup\Helper\Data $setupHelper,
        PageRepository $pageRepository,
        FileReader $fileReader,
        DirReader $dirReader
    ) {
        $this->blockFactory = $modelBlockFactory;
        $this->pageFactory = $modelPageFactory;
        $this->conditionsHelper = $conditionsHelper;
        $this->setupHelper = $setupHelper;
        $this->pageRepository = $pageRepository;
        $this->fileReader = $fileReader;
        $this->dirReader = $dirReader;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setupHelper->log('------------------ ');
        $setup->startSetup();

        $files = $this->setupHelper->getUpdatesFileList(__DIR__ . '/updates', $context, 'Alpine_CmsSetup');
        foreach ($files as $file) {
            $this->setupHelper->log('Running Upgrade File: ' . $file);
            require_once($file);
        }

        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            $this->addCmsPages(['Resources']);
        }

        if (version_compare($context->getVersion(), '0.1.5', '<')) {
            $this->addCmsPages(['Company']);
        }

        if (version_compare($context->getVersion(), '0.1.6', '<')) {
            $this->addCmsPages(['About Slides']);
        }

        if (version_compare($context->getVersion(), '0.1.7', '<')) {
            $this->addCmsPages(['Support']);
            $page = $this->pageRepository->getById('support')->setContentHeading('');
            $this->pageRepository->save($page);
        }

        if (version_compare($context->getVersion(), '0.1.68', '<')) {
            $this->addCmsPages(['Why Accuride']);
        }

        if (version_compare($context->getVersion(), '0.4.0', '<')) {
            $this->addCmsPages(['Slides']);
        }

        if (version_compare($context->getVersion(), '0.3.0', '<')) {
            $this->addCmsPages(['Shop']);
            $layout = <<<LAYOUT
<referenceContainer name="main.content" >
    <block class="Alpine\Theme\Block\SliderSelector" name="slider_selector" template="Magento_Theme::slider_selector_home.phtml"/>
</referenceContainer>
LAYOUT;


            $page = $this->pageRepository->getById('shop')->setLayoutUpdateXml($layout);
            $this->pageRepository->save($page);
        }

        if (version_compare($context->getVersion(), '0.5.0', '<')) {
            $this->addCmsPages(['Products']);
        }
        
        if (version_compare($context->getVersion(), '0.6.0', '<')) {
            $this->addCmsPages(['Cads']);
        }
        
        if (version_compare($context->getVersion(), '0.7.0', '<')) {
            $this->addCmsPages(['Specialty Slides']);
        }
        
        if (version_compare($context->getVersion(), '0.8.0', '<')) {
            $this->addCmsPages(['Customer Stories']);
        }

        if (version_compare($context->getVersion(), '0.9.0', '<')) {
            $this->addCmsPages(['Media']);
        }
        
        if (version_compare($context->getVersion(), '0.10.0', '<')) {
            $this->addCmsPages(['Literature']);
        }
        
        if (version_compare($context->getVersion(), '0.11.0', '<')) {
            $this->addCmsPages(['Careers']);
        }

        if (version_compare($context->getVersion(), '0.13.0', '<')) {
            $this->addCmsPages(['Oem Direct']);
        }

        if (version_compare($context->getVersion(), '0.12.2', '<')) {
            $this->addCmsPages(['Resources']);
        }

        if (version_compare($context->getVersion(), '0.13.1', '<')) {
            $this->addCmsPages(['Literature']);
        }

        if (version_compare($context->getVersion(), '0.13.2', '<')) {
            $this->addCmsPages(['Support']);
            $page = $this->pageRepository->getById('support')->setContentHeading('');
            $this->pageRepository->save($page);
        }

        if (version_compare($context->getVersion(), '0.15.0', '<')) {
            $this->addCmsPages(['About']);
        }

        if (version_compare($context->getVersion(), '0.17.0', '<')) {

            $pages = [
                'about',
                'videos',
                'oem-direct',
                'careers',
                'literature',
                'media',
                'customer-stories',
                'specialty-slides',
                'cads',
                'store',
                'slide-guides',
                'why-accuride',
                'about-slides',
                'company',
                'resources',
                'support',
                'warranty',
            ];

            foreach ($pages as $pageId){

                try {
                    $page = $this->pageRepository->getById($pageId);
                    $this->setupHelper->log('Updating page: ' . $pageId);
                    $page->setPageLayout('full-width');
                    $this->pageRepository->save($page);
                } catch (NoSuchEntityException $e) {
                    $this->setupHelper->log($e->getMessage());
                }
            }
        }

        if (version_compare($context->getVersion(), '0.18.1', '<')) {
            $this->addCmsPages(['Videos']);
        }

        if (version_compare($context->getVersion(), '0.18.2', '<')) {
            $this->addCmsPages(['Support']);
            $page = $this->pageRepository->getById('support')->setContentHeading('');
            $this->pageRepository->save($page);
        }

        $setup->endSetup();
        $this->setupHelper->log('------------------ ');
    }

    /**
     * @return \Magento\Cms\Model\Block
     */
    public function createBlock()
    {
        return $this->blockFactory->create();
    }

    /**
     * @return \Magento\Cms\Model\Page
     */
    public function createPage()
    {
        return $this->pageFactory->create();
    }

    /**
     * Add or update cms pages
     *
     * @param array $pages
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function addCmsPages(array $pages)
    {
        $pagesDir = $this->dirReader->getModuleDir(false, 'Alpine_CmsSetup') .
            DIRECTORY_SEPARATOR .
            self::SETUP_FOLDER .
            DIRECTORY_SEPARATOR .
            self::HTML_FOLDER .
            DIRECTORY_SEPARATOR;

        foreach ($pages as $pageName) {
            $filePath = $pagesDir . $pageName . self::HTML_SUFFIX;

            if (!$this->fileReader->fileExists($filePath)) {
                continue;
            }

            $content = $this->fileReader->read($filePath);
            if (empty($content)) {
                continue;
            }

            $pageId = preg_replace("/[^A-Za-z0-9]/", '-', strtolower($pageName));
            $pageId = preg_replace('/-+/', '-', $pageId);

            try {
                $page = $this->pageRepository->getById($pageId);
                $this->setupHelper->log('Page already exists: ' . $pageId);
            } catch (NoSuchEntityException $e) {
                $page = $this->pageFactory->create();
                $title = str_replace('_', ' ', $pageName);
                $this->setupHelper->log('Adding page: ' . $pageId);

                $page->setIdentifier($pageId)
                    ->setTitle($title)
                    ->setIsActive(true)
                    ->setPageLayout(self::NEW_PAGE_LAYOUT)
                    ->setStores([Store::DEFAULT_STORE_ID]);
            }

            $page->setContent($content);
            $this->pageRepository->save($page);
        }
    }
}
