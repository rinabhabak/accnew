<?php
/**
 * Alpine_Cms
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Aleksandr Mikhailov <aleksandr.mikhailov@alpineinc.com>
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Cms\Block;

use Magento\Cms\Block\BlockFactory;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ProductInfoStatic
 *
 * @category    Alpine
 * @package     Alpine_Cms
 */
class ProductInfoStatic extends Template
{
    /**
     * Identifiers of CMS Blocks, content of which need to render as popups
     *
     * @var array
     */
    protected $cmsBlocksIdentifiers = ['warranty', 'support', 'shipping'];

    /**
     * CMS Blocks Collection Factory
     *
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * ProductInfoStatic constructor.
     *
     * @param Context $context
     * @param BlockFactory $blockFactory
     * @param array $data
     */
    public function __construct(Context $context, BlockFactory $blockFactory, array $data = [])
    {
        parent::__construct($context, $data);
        $this->blockFactory = $blockFactory;
    }

    /**
     * Get CMS Blocks, content of which need to render as popups
     *
     * @return array
     */
    public function getCmsBlocks()
    {
        $result = [];
        foreach ($this->cmsBlocksIdentifiers as $cmsPagesIdentifier) {
            /**
             * $block \Magento\Cms\Block\Block
             */
            $block = $this->blockFactory->create()->setBlockId($cmsPagesIdentifier);

            $obj = new DataObject();
            $obj->setData('id', $cmsPagesIdentifier);
            $obj->setData('content', $block->toHtml());
            $result[] = $obj;
        }
        return $result;
    }
}
