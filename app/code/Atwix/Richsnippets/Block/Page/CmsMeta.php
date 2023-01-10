<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Block\Page;

use Atwix\Richsnippets\Helper\Cms as CmsHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class CmsMeta extends Template
{
    /**
     * @var \Atwix\Richsnippets\Helper\Data
     */
    protected $helper;

    public function __construct(
        Context $context,
        CmsHelper $helper,
        array $data
    )
    {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function toHtml()
    {
        $this->makeMetaTags();
        return parent::toHtml();
    }

    /**
     * Passes metaTags to the template
     */
    protected function makeMetaTags()
    {
       $this->assign('metaTags', $this->helper->generateCmsTags($this->getLayout()));
    }
}