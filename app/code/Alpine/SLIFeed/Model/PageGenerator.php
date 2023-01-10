<?php
/**
 * Generate pages XML / Alpine_SLIFeed
 *
 * @category    Alpine
 * @package     Alpine_SLIFeed
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc
 * @author      Evgeniy Derevyanko <evgeniy.derevyanko@alpineinc.com>
 * @author      Vasiliy Abolmasov <vasiliy.abolmasov@cyberhull.com>
 */


namespace Alpine\SLIFeed\Model;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use SLI\Feed\Helper\XmlWriter;
use SLI\Feed\Helper\GeneratorHelper;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Alpine\SLIFeed\Helper\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class PageGenerator
 *
 * @category    Alpine
 * @package     Alpine_SLIFeed
 */
class PageGenerator implements \SLI\Feed\Model\Generators\GeneratorInterface
{
    /**
     * Feed generation helper
     *
     * @var GeneratorHelper
     */
    protected $generatorHelper;
    
    /**
     * Store Manager
     * 
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * Search criteria
     * 
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    
    /**
     * page repository
     * 
     * @var PageRepositoryInterface
     */
    protected $cmsPageRepository;
    
    /**
     * Page helper
     * 
     * @var Page
     */
    protected $pageHelper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * List of exported page attributes
     * 
     * @var array
     */
    const ATTRIBUTES = [
        'page_id'=>'identifier', 
        'url' => '', 
        'title' => 'title',
        'description' => 'meta_description',
        'image' => '', 
        'keywords' => 'meta_keywords'
    ];
    
    /**
     * Single entity name in XML
     * 
     * @var string
     */
    const ENTITY_XML_NAME = 'entry';
    
    /**
     * list entity name in XML
     * 
     * @var string
     */
    const ENTITY_LIST_XML_NAME = 'pages';
    
    /**
     * New main XML tag
     * 
     * @var string
     */
    const NEW_MAIN_XML_TAG = 'cms';

    /**
     * List of excluded pages
     *
     * @var string
     */
    const EXCLUDED_PAGES = 'alpine_feed_export/general/sli_feed_cron_excluded_pages';

    /**
     * PageGenerator constructor.
     *
     * @param GeneratorHelper $generatorHelper
     * @param StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PageRepositoryInterface $cmsPageRepository
     * @param Page $pageHelper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        GeneratorHelper $generatorHelper,
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PageRepositoryInterface $cmsPageRepository,
        Page $pageHelper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->generatorHelper = $generatorHelper;
        $this->storeManager = $storeManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cmsPageRepository = $cmsPageRepository;
        $this->pageHelper = $pageHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Generate pages XML export 
     * 
     * @param int $storeId
     * @param XmlWriter $xmlWriter
     * @param LoggerInterface $logger
     * @return boolean
     */
    public function generateForStoreId($storeId, XmlWriter $xmlWriter, LoggerInterface $logger)
    {
        $logger->debug(sprintf('[%s] Starting page XML generation', $storeId));
        
        $xmlWriter->startElement(self::NEW_MAIN_XML_TAG);
        $xmlWriter->startElement(self::ENTITY_LIST_XML_NAME);

        $excludedPages = $this->scopeConfig->getValue(self::EXCLUDED_PAGES);
        $excludedPagesArray = explode(',', $excludedPages);
       
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', 1, 'eq')
            ->addFilter('store_id', [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $storeId], 'in')
            ->addFilter('identifier', $excludedPagesArray, 'nin')
            ->create();

        try {
            $cmsPageCollection = $this->cmsPageRepository
                ->getList($searchCriteria)
                ->getItems();

            foreach ($cmsPageCollection as $item) {
                $this->writePage($xmlWriter, $item);
            }
        } catch (\Exception $e) {
            $logger->critical($e->getMessage());
        }

        $logger->debug('Finished writing CMS page');
        
        $xmlWriter->endElement();//pages
        $xmlWriter->endElement();//cms

        return true;
    }

    /**
     * Write XML for a cms page
     *
     * @param XmlWriter $xmlWriter
     * @param \Magento\Cms\Model\Page $page
     * @return void
     */
    protected function writePage(XmlWriter $xmlWriter, $page)
    {
        $writeElement = function ($name, $value) use ($xmlWriter) {
            $xmlWriter->startElement($name);
            $xmlWriter->text($value);
            $xmlWriter->endElement();
        };

        $xmlWriter->startElement(self::ENTITY_XML_NAME);

        foreach (self::ATTRIBUTES as $attributeKey => $attributeName) {
            switch ($attributeKey) {
                case 'url':
                    $writeElement($attributeKey, $this->pageHelper->getPageUrl($page->getId()));
                    break;
                
                case 'image':
                    $writeElement($attributeKey, $attributeName);
                    break;
                
                default: 
                    $writeElement($attributeKey, $page->getData($attributeName));
                    break;
            }
        }

        $xmlWriter->endElement();
    }
}
