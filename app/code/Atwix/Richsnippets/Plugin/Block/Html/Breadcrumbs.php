<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */

namespace Atwix\Richsnippets\Plugin\Block\Html;

use Atwix\Richsnippets\Helper\Data as SnippetsHelper;
use Magento\Framework\View\Element\Template;

/**
 * Class Breadcrumbs
 */
class Breadcrumbs
{
    /**
     * @var SnippetsHelper
     */
    protected $helper;

    /**
     * Breadcrumbs constructor
     *
     * @param SnippetsHelper $helper
     */
    public function __construct(
        SnippetsHelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Injects helper to the breadcrumbs block to provide possibility
     * to use the helper inside of the breadcrumbs template
     *
     * @param Template $subject
     */
    public function afterSetTemplateContext(Template $subject)
    {
        $subject->assign('snippetsHelper', $this->helper);
    }
}
