<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */

namespace Atwix\Richsnippets\Block\Page;

use Atwix\Richsnippets\Helper\Data as SnippetsHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class SearchBox extends Template
{
    /**
     * @var SnippetsHelper
     */
    protected $helper;

    /**
     * SearchBox constructor.
     * @param Context $context
     * @param SnippetsHelper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SnippetsHelper $helper,
        array $data = [])
    {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->helper->isSnippetEnabled('search_box')) {

            return '';
        }

        return parent::toHtml();
    }

    /**
     * Get search URL
     *
     * @return string
     */
    public function getSearchUrl()
    {
        $searchURI = 'catalogsearch/result/?q={' . $this->getSearchInputName() . '}';
        if (trim($this->helper->getConfigurationValue('search_box/alternate_uri')) != '') {
            $searchURI = $this->helper->getConfigurationValue('search_box/alternate_uri');
        }

        return $this->getBaseUrl() . $searchURI;
    }

    /**
     * Get search input name
     *
     * @return string
     */
    public function getSearchInputName()
    {
        $input_name = 'q';
        if (trim(htmlspecialchars($this->helper->getConfigurationValue('search_box/alternate_input_name'))) != '') {
            $input_name = $this->helper->getConfigurationValue('search_box/alternate_input_name');
        }

        return $input_name;
    }
}