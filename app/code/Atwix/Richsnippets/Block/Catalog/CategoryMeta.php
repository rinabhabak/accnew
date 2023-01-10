<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Block\Catalog;

use Atwix\Richsnippets\Helper\Category as CategoryHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class CategoryMeta extends Template
{
    /**
     * @var \Atwix\Richsnippets\Helper\Data
     */
    protected $helper;

    public function __construct(
        Context $context,
        CategoryHelper $helper,
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
       $this->assign('metaTags', $this->helper->generateCategoryMetatags());
    }
}