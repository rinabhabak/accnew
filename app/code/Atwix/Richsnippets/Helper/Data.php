<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Url as Url;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Breadcrumbs;
use Atwix\Richsnippets\Service\SerializerService;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    const SNIPPET_TYPE_FOOTER       = 'footer';
    const SNIPPET_TYPE_VISIBLE      = 'visible';
    const SNIPPET_TYPE_SIDEBAR      = 'sidebar';
    const SNIPPET_TYPE_JSON         = 'json';
    const SNIPPET_POSITION_AFTER    = 'after';
    const SNIPPET_POSITION_BEFORE   = 'before';
    const BC_SNIPPET_TYPE_SCHEMA    = 'schema';
    const BC_SNIPPET_TYPE_JSON      = 'json';
    const BC_SNIPPET_TYPE_RDF       = 'rdf';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var SerializerService
     */
    protected $serializer;

    /**
     * Data constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Url $url
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Url $url,
        SerializerService $serializer
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->serializer = $serializer;
    }
    
    /**
     * Returns true if extension is enabled in general settings
     *
     * @return mixed
     */
    public function getExtensionIsEnabled()
    {
        return $this->getConfigurationValue('general/enabled');
    }

    /**
     * Returns breadcrumbs snippet type from config
     *
     * @return string
     */
    public function getBreadcrumbsSnippetType()
    {
        return $this->getConfigurationValue('breadcrumbs/type');
    }

    /**
     * Returns first breadcrumb name from config if specified
     *
     * @param $title
     * @return string
     */
    public function getFirstBreadcrumbLabel($title)
    {
        return $this->getConfigurationValue('breadcrumbs/custom_home')
            && $this->isSnippetEnabled('breadcrumbs') ?
            $this->getConfigurationValue('breadcrumbs/custom_home_title') : $title;
    }

    /**
     * Returns true if breadcrumbs in JSON format enabled
     *
     * @return bool
     */
    public function getJsonBreadcrumbsEnabled()
    {
        return ($this->isSnippetEnabled('breadcrumbs') &&
            $this->getConfigurationValue('breadcrumbs/type') == static::BC_SNIPPET_TYPE_JSON);
    }

    /**
     * Generates breadcrumbs JSON based on the standard breadcrumbs
     *
     * @param Breadcrumbs $breadcrumbsBlock
     * @return string
     */
    public function generateBreadcrumbsJson($breadcrumbsBlock)
    {
        $jsonSnippet = array();
        $cacheKeyInfo = $breadcrumbsBlock->getCacheKeyInfo();
        $breadcrumbs = $this->serializer->unserialize((base64_decode($cacheKeyInfo['crumbs'])));

        if (!empty($breadcrumbs)) {
            $jsonSnippet['@context'] = 'http://schema.org';
            $jsonSnippet['@type'] = 'BreadcrumbList';
            $position = 1;
            $breadcrumbsItems = [];
            foreach ($breadcrumbs as $breadcrumb) {
                if (isset($breadcrumb['link'])) {
                    if ($position == 1) {
                        $item['item']['name'] = $this->getFirstBreadcrumbLabel($breadcrumb['label']);
                    } else {
                        $item['item']['name'] = $breadcrumb['label'];
                    }
                    $item['position'] = $position++;
                    $item['@type'] = 'ListItem';
                    $item['item']['@id'] = $breadcrumb['link'];

                    array_push($breadcrumbsItems, $item);
                }
            }
            $jsonSnippet['itemListElement'] = $breadcrumbsItems;
        }
        return json_encode($jsonSnippet);
    }

    /**
     * Returns system configuration value
     *
     * @param $key
     * @param null $store
     * @return mixed
     */
    public function getConfigurationValue($key, $store = null)
    {
        return $this->scopeConfig->getValue(
            'atwix_richsnippets/' . $key,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Checks if the snippet can be used
     *
     * @param $snippet
     * @return bool
     */
    public function isSnippetEnabled($snippet)
    {
        return $this->getExtensionIsEnabled() && $this->getConfigurationValue($snippet . '/enabled');
    }

    /**
     * @return mixed
     */
    public function isProductReviewFixEnabled()
    {
        return (bool) $this->getConfigurationValue('general/review_fix_enabled');
    }
}