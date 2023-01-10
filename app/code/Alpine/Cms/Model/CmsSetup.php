<?php
/**
 * Alpine_Cms
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Denis Furman <denis.furman@alpineinc.com>
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */

namespace Alpine\Cms\Model;

use Magento\Cms\Api\BlockRepositoryInterface as CmsBlockRepository;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Store\Model\Store;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\Data\PageInterface;

/**
 * Class SetupCms.
 *
 * @package Magazijnshopper\Cms\Model
 */
class CmsSetup
{
    /**
     * Block factory.
     *
     * @var BlockInterfaceFactory
     */
    protected $blockFactory;

    /**
     * Block repository.
     *
     * @var CmsBlockRepository
     */
    protected $cmsBlockRepository;

    /**
     * SampleDataContext.
     *
     * @var SampleDataContext
     */
    protected $sampleDataContext;

    /**
     * Fixture manager.
     *
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * CSV reader.
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    protected $pageFactory;
    protected $pageRepository;

    /**
     * SetupCms Constructor.
     *
     * @param BlockInterfaceFactory $blockFactory
     * @param CmsBlockRepository $cmsBlockRepository
     * @param SampleDataContext $sampleDataContext
     * @param PageRepositoryInterface $pageRepository
     * @param PageInterfaceFactory $pageFactory
     */
    public function __construct(
        BlockInterfaceFactory $blockFactory,
        CmsBlockRepository $cmsBlockRepository,
        SampleDataContext $sampleDataContext,
        PageRepositoryInterface $pageRepository,
        PageInterfaceFactory $pageFactory

    )
    {
        $this->blockFactory = $blockFactory;
        $this->cmsBlockRepository = $cmsBlockRepository;
        $this->sampleDataContext = $sampleDataContext;
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Load data from CSV.
     *
     * @param string $fixture
     *
     * @return array|bool
     */
    protected function loadData($fixture)
    {
        $fileName = $this->fixtureManager->getFixture($fixture);
        if (!file_exists($fileName)) {
            $this->addExecutionMessage('File not found:' . $fileName . '.');

            return false;
        }

        return $this->csvReader->getData($fileName);
    }

    /**
     * Load blocks.
     *
     * @param string $fixture
     *
     * @return void
     * @throws \Exception
     */
    public function loadAndInstallBlock($fixture)
    {
        $rows = $this->loadData($fixture);
        array_shift($rows);
        $header = [
            BlockInterface::TITLE,
            BlockInterface::IDENTIFIER,
            BlockInterface::CONTENT,
        ];
        foreach ($rows as $row) {
            $block = [];
            foreach ($row as $key => $value) {
                $block[$header[$key]] = $value;
            }
            $this->installCmsBlock($block);
        }
    }

    /**
     * Install CMS block (create/update).
     *
     * @param [] $block
     *
     * @return void
     */
    public function installCmsBlock($block)
    {
        try {
            $cmsBlock = $this->cmsBlockRepository->getById($block[BlockInterface::IDENTIFIER]);
        } catch (NoSuchEntityException $e) {
            $cmsBlock = $this->blockFactory->create();
        }

        $cmsBlock->setIdentifier($block[BlockInterface::IDENTIFIER])
            ->setTitle($block[BlockInterface::TITLE])
            ->setContent($block[BlockInterface::CONTENT])
            ->setData('stores', Store::DEFAULT_STORE_ID);
        $this->cmsBlockRepository->save($cmsBlock);
    }

    /**
     * Load a CMS block by id.
     *
     * @param int/string $identifier
     *
     * @return \Magento\Cms\Api\Data\BlockInterface|bool
     */
    public function getCmsBlockById($identifier)
    {
        try {
            $cmsBlock = $this->cmsBlockRepository->getById($identifier);
            return $cmsBlock;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Function save cms block.
     *
     * @param \Magento\Cms\Api\Data\BlockInterface $cmsBlock cmsBlock
     *
     * @return void
     */
    public function saveCmsBlock($cmsBlock)
    {
        /* @var $cmsBlockRepository CmsBlockRepository */
        $this->cmsBlockRepository->save($cmsBlock);
    }

    /**
     * Load a CMS page by id
     *
     * @param string $identifier
     * @return PageInterface|null
     */
    public function getCmsPageById($identifier)
    {
        try {
            $result = $this->pageRepository->getById($identifier);
        } catch (NoSuchEntityException $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * Install a CMS page (create/update).
     *
     * @param array $page
     */
    public function installCmsPage($page)
    {
        /* @var PageInterface $cmsPage */
        try {
            $cmsPage = $this->pageRepository->getById($page[PageInterface::IDENTIFIER]);
        } catch (NoSuchEntityException $e) {
            $cmsPage = $this->pageFactory->create();
        }

        $cmsPage->setIdentifier($page[PageInterface::IDENTIFIER])
            ->setTitle($page[PageInterface::TITLE])
            ->setContentHeading($page[PageInterface::CONTENT_HEADING])
            ->setContent($page[PageInterface::CONTENT])
            ->setPageLayout($page[PageInterface::PAGE_LAYOUT])
            ->setData('stores', [Store::DEFAULT_STORE_ID]);
        $this->pageRepository->save($cmsPage);
    }

    /**
     * Load pages.
     *
     * @param string $fixture
     *
     * @throws \Exception
     */
    public function loadPages($fixture)
    {
        $rows = $this->loadData($fixture);
        array_shift($rows);
        $header = [
            'store',
            PageInterface::IDENTIFIER,
            PageInterface::TITLE,
            PageInterface::CONTENT_HEADING,
            PageInterface::CONTENT,
            PageInterface::PAGE_LAYOUT
        ];
        foreach ($rows as $row) {
            $page = [];
            foreach ($row as $key => $value) {
                $page[$header[$key]] = $value;
            }
            $this->installCmsPage($page);
        }
    }
}