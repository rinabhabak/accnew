<?php
/**
 * Alpine_VideoWidget
 *
 * @category    Alpine
 * @package     Alpine_VideoWidget
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Daria Mishina <daria.mishina@alpineinc.com>
 */
namespace Alpine\VideoWidget\Block;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Video extends Template implements BlockInterface
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('video.phtml');
    }
}