<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Block\Page;

use Atwix\Richsnippets\Helper\Organization as OrganizationHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class OrganizationJson extends Template
{
    /**
     * @var OrganizationHelper;
     */
    protected $helper;

    public function __construct(
        Context $context,
        OrganizationHelper $helper,
        array $data
    )
    {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        if ($this->helper->isSnippetEnabled('organization')) {
            $this->setData('template', 'general/head_json.phtml');
            parent::_construct();
        }
    }

    public function toHtml()
    {
        $this->makeOrganizationJson();
        return parent::toHtml();
    }

    /**
     * Passes organisation info JSON to the template
     */
    public function makeOrganizationJson()
    {
        $this->assign('headJson', $this->helper->makeOrganizationJson());
    }
}