<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */

namespace Atwix\Richsnippets\Plugin\Block\Product;

use Atwix\Richsnippets\Helper\Product as SnippetsHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;

/**
 * Class View
 */
class View
{
    /**
     * @var SnippetsHelper
     */
    protected $helper;

    /**
     * View constructor
     *
     * @param SnippetsHelper $helper
     */
    public function __construct(
        SnippetsHelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Assign snippets data to product view template
     *
     * @param Template $templateContext
     * @throws LocalizedException
     */
    public function afterSetTemplateContext(Template $templateContext)
    {
        $templateContext->assign('productSnippets', $this->helper->getProductSnippets());
    }
}
