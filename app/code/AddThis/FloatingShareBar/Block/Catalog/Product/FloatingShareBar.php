<?php

namespace AddThis\FloatingShareBar\Block\Catalog\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use AddThis\FloatingShareBar\Helper\Data as HelperData;
use Magento\Framework\ObjectManagerInterface;

class FloatingShareBar extends Template
{
    protected $helperData;
    protected $objectManager;

    public function __construct(
        Context $context,
        HelperData $helperData,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    public function getBlockLabel()
    {
        return $this->helperData->getBlockLabel();
    }

    public function getDesktopPosition()
    {
        return $this->helperData->getDesktopPosition();
    }

    public function getMobilePosition()
    {
        return $this->helperData->getMobilePosition();
    }

    public function getCounts()
    {
        return $this->helperData->getCounts();
    }

    public function getNumPreferredServices()
    {
        return $this->helperData->getNumPreferredServices();
    }

    public function getStyle()
    {
        return $this->helperData->getStyle();
    }

    public function getMobileButtonSize()
    {
        return $this->helperData->getMobileButtonSize();
    }

    public function getTheme()
    {
        return $this->helperData->getTheme();
    }

    protected function _toHtml()
    {
        if ($this->helperData->getEnable()) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    public function getCollection()
    {
        $model = $this->objectManager->create('AddThis\FloatingShareBar\Model\FloatingShareBar');
        $collection = $model->getCollection();
        return $collection;
    }
}
