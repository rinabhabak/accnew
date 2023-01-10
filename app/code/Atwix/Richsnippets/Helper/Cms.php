<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Helper;

use Atwix\Richsnippets\Helper\Data as SnippetsHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Url as Url;
use Magento\Framework\View\Layout;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Cms
 */
class Cms extends SnippetsHelper
{
    const XML_PATH_SITENAME_ENABLED = 'cms/website_name_enabled';
    const XML_PATH_SITENAME_SITE_NAME = 'general/site_name';
    const XML_PATH_SITENAME_ALTERNATIVE = 'cms/alternative_website_name';

    /**
     * Inits twitter cards and open graph meta tags generation
     *
     * @param Layout $layout
     * @return array
     */
    public function generateCmsTags($layout)
    {
        $cmsMetaTags = [];

        if (!$this->getExtensionIsEnabled()) {
            return $cmsMetaTags;
        }

        if ($openGraphInfo = $this->getOpenGraphInfo($layout)) {
            $cmsMetaTags = array_merge($openGraphInfo, $cmsMetaTags);
        }

        if ($twitterInfo = $this->getTwitterInfo($layout)) {
            $cmsMetaTags = array_merge($twitterInfo, $cmsMetaTags);
        }

        return $cmsMetaTags;
    }

    /**
     * Generates open graph meta tags
     *
     * @param Layout $layout
     * @return bool|array
     */
    public function getOpenGraphInfo($layout)
    {
        $openGraphMeta = [];
        if (!$this->getConfigurationValue('cms/opengraph')) {
            return false;
        }

        $openGraphTitle = $this->getConfigurationValue('cms/opengraph_title');
        $openGraphDescription = $this->getConfigurationValue('cms/opengraph_description');
        $openGraphLogo = $this->getConfigurationValue('cms/opengraph_logo');
        $openGraphLogoURL = $this->getConfigurationValue('cms/opengraph_logo_url');

        $pageMetaInfo = $this->getPageMetaInfo($layout);

        if ($openGraphTitle) {
            $openGraphMeta['og:title'] = $pageMetaInfo['title']; // TODO: If it's home page use a store name or so as a title
        }

        if ($openGraphDescription) {
            $openGraphMeta['og:description'] = $pageMetaInfo['description'];
        }

        if ($openGraphLogo && !empty($openGraphLogoURL)) {
            $openGraphMeta['og:image'] = $openGraphLogoURL;
        }

        if (count($openGraphMeta) != 0) {
            $openGraphMeta['og:url'] = $this->url->getCurrentUrl();
            $openGraphMeta['og:type'] = $pageMetaInfo['type'];
        } else {
            return false;
        }

        return $openGraphMeta;
    }

    /**
     * Generates twitter cards meta tags
     *
     * @param Layout $layout
     * @return bool|array
     */
    public function getTwitterInfo($layout)
    {
        $twitterMeta = [];
        if (!$this->getConfigurationValue('cms/twitter')) {
            return false;
        }

        $twitterUsername = $this->getConfigurationValue('general/twitter_username');
        $twitterLogoUrl = $this->getConfigurationValue('cms/twitter_logo_url');

        if (empty($twitterUsername)) {
            return false;
        }

        $pageMetaInfo = $this->getPageMetaInfo($layout);
        $twitterMeta['twitter:card'] = 'summary';
        $twitterMeta['twitter:site'] = '@' . $twitterUsername;
        $twitterMeta['twitter:title'] = $pageMetaInfo['title'];
        $twitterMeta['twitter:text:description'] = trim($pageMetaInfo['description']) != '' ?
            $pageMetaInfo['description'] : $this->scopeConfig->getValue('design/head/default_description');
        if (!empty($twitterLogoUrl)) {
            $twitterMeta['twitter:image'] = $twitterLogoUrl;
        }

        return $twitterMeta;
    }

    /**
     * Returns page title, description and og type
     *
     * @param Layout $layout
     * @return array
     */
    protected function getPageMetaInfo($layout)
    {
        $metaInfo = ['title' => '', 'description' => ''];
        $cmsPageBlock = $layout->getBlock('cms_page');
        if ($cmsPageBlock) {
            $page = $cmsPageBlock->getPage();
            $pageTitle = $page->getData('title');
            $pageDescription = $page->getData('meta_description');
            $metaInfo = [
                'title' => !empty($pageTitle) ? $pageTitle : '',
                'description' => !empty($pageDescription) ? $pageDescription : '',
                'type' => ($page->getData('identifier') == 'home') ? 'website' : 'article'
            ];
        }

        return $metaInfo;
    }

    /**
     * Generates JSON sitename snippets
     *
     * @return string
     */
    public function generateSitenameSnippetsJSON()
    {
        if (!$siteName = $this->getConfigurationValue(self::XML_PATH_SITENAME_SITE_NAME)) {
            return '';
        }
        $siteNameSnippets['@type'] = 'WebSite';
        $siteNameSnippets['name'] = $siteName;
        $siteNameSnippets['@context'] = 'http://schema.org';
        $siteNameSnippets['url'] = $this->storeManager->getStore()->getBaseUrl();

        if ($websiteNameAlt = $this->getConfigurationValue(self::XML_PATH_SITENAME_ALTERNATIVE)) {
            $siteNameSnippets['alternateName'] = $websiteNameAlt;
        }

        return json_encode($siteNameSnippets);
    }
}