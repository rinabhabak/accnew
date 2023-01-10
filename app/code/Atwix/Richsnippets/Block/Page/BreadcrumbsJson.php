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

class BreadcrumbsJson extends Template
{
    /**
     * @var \Atwix\Richsnippets\Helper\Data
     */
    protected $helper;

    public function __construct(
        Context $context,
        SnippetsHelper $helper,
        array $data
    )
    {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        if ($this->helper->getJsonBreadcrumbsEnabled()) {
            $this->setData('template', 'general/head_json.phtml');
            parent::_construct();
        }
    }

    public function toHtml()
    {
        $this->makeBreadcrumbsJson();
        return parent::toHtml();
    }

    /**
     * Passes organisation info JSON to the template
     */
    public function makeBreadcrumbsJson()
    {
        /** @var \Magento\Theme\Block\Html\Breadcrumbs $origBreadcrumbsBlock */
        $origBreadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        if ($origBreadcrumbsBlock) {
            $this->assign('headJson', $this->helper->generateBreadcrumbsJson($origBreadcrumbsBlock));
        }
    }
}