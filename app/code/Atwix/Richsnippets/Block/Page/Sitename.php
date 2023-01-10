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

class Sitename extends Template
{
    /**
     * @var  CmsHelper
     */
    protected $helper;

    /**
     * Sitename constructor.
     * @param Context $context
     * @param CmsHelper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CmsHelper $helper,
        array $data = [])
    {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        if ($this->helper->getExtensionIsEnabled() &&
            $this->helper->getConfigurationValue(CmsHelper::XML_PATH_SITENAME_ENABLED)
        ) {
            $this->setData('template', 'general/head_json.phtml');
            parent::_construct();
        }
    }

    public function toHtml()
    {
        $this->makeSitenameSnippetsJson();
        return parent::toHtml();
    }

    public function makeSitenameSnippetsJson()
    {
        $this->assign('headJson', $this->helper->generateSitenameSnippetsJSON());
    }
}

