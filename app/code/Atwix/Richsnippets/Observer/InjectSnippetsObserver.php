<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */

namespace Atwix\Richsnippets\Observer;

use Atwix\Richsnippets\Block\Catalog\CategoryVisible;
use Atwix\Richsnippets\Helper\Data as SnippetsHelper;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;

/**
 * Class InjectSnippetsObserver
 */
class InjectSnippetsObserver implements ObserverInterface
{
    const FOOTER_ELEMENT_NAME  = 'footer-container';
    const CONTENT_ELEMENT_NAME = 'content';
    const SIDEBAR_ELEMENT_NAME = 'sidebar.additional';

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var SnippetsHelper
     */
    protected $snippetsHelper;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $elementName;

    /**
     * InjectSnippetsObserver constructor
     *
     * @param Http $request
     * @param SnippetsHelper $snippetsHelper
     * @param Registry $registry
     */
    public function __construct(
        Http $request,
        SnippetsHelper $snippetsHelper,
        Registry $registry
    ) {
        $this->request = $request;
        $this->registry = $registry;
        $this->snippetsHelper = $snippetsHelper; // TODO: inject the actual helper
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        //observer should run on category view pages only
        if ($this->request->getFullActionName() !== 'catalog_category_view') {

            return $this;
        }

        if (!$this->snippetsHelper->isSnippetEnabled('category')) {

            return $this;
        }

        $snippetType = $this->snippetsHelper->getConfigurationValue('category/type');
        $templateName = 'catalog/category/visible.phtml';
        $snippetPosition = $this->snippetsHelper->getConfigurationValue('category/position');

        switch($snippetType) {
            case SnippetsHelper::SNIPPET_TYPE_VISIBLE :
                $targetBlockName = self::CONTENT_ELEMENT_NAME;
                break;
            case SnippetsHelper::SNIPPET_TYPE_FOOTER :
                $targetBlockName = self::FOOTER_ELEMENT_NAME;
                break;
            case SnippetsHelper::SNIPPET_TYPE_SIDEBAR :
                $targetBlockName = self::SIDEBAR_ELEMENT_NAME;
                $templateName = 'catalog/category/sidebar.phtml';
                $snippetPosition = SnippetsHelper::SNIPPET_POSITION_AFTER;
                break;
            default :
                $targetBlockName = false;
        }

        if ($targetBlockName && $observer->getData('element_name') == $targetBlockName) {
            /** @var Layout $layout */
            $layout = $observer->getData('layout');
            /** @var DataObject $transport */
            $transport = $observer->getData('transport');

            $elementHtml = $transport->getData('output');
            $snippetBlock = $layout->createBlock(CategoryVisible::class);
            if ($templateName) {
                $snippetBlock->setTemplate($templateName);
            }
            $snippetHtml = $snippetBlock->toHtml();

            if ($snippetPosition == SnippetsHelper::SNIPPET_POSITION_AFTER) {
                $transport->setData('output', $elementHtml . $snippetHtml);
            } else {
                $transport->setData('output', $snippetHtml . $elementHtml);
            }
        }

        return $this;
    }
}
