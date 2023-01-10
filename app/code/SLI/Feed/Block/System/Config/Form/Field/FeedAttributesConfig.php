<?php
/**
 * Copyright (c) 2015 S.L.I. Systems, Inc. (www.sli-systems.com) - All Rights Reserved
 * This file is part of Learning Search Connect.
 * Learning Search Connect is distributed under a limited and restricted
 * license � please visit www.sli-systems.com/LSC for full license details.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE. TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO
 * EVENT WILL SLI BE LIABLE TO YOU OR ANY OTHER PARTY FOR ANY GENERAL, DIRECT,
 * INDIRECT, SPECIAL, INCIDENTAL OR CONSEQUENTIAL LOSS OR DAMAGES OF ANY
 * CHARACTER ARISING OUT OF THE USE OF THE CODE AND/OR THE LICENSE INCLUDING
 * BUT NOT LIMITED TO PERSONAL INJURY, LOSS OF DATA, LOSS OF PROFITS, LOSS OF
 * ASSIGNMENTS, DATA OR OUTPUT FROM THE SERVICE BEING RENDERED INACCURATE,
 * FAILURE OF CODE, SERVER DOWN TIME, DAMAGES FOR LOSS OF GOODWILL, BUSINESS
 * INTERRUPTION, COMPUTER FAILURE OR MALFUNCTION, OR ANY AND ALL OTHER DAMAGES
 * OR LOSSES OF WHATEVER NATURE, EVEN IF SLI HAS BEEN INFORMED OF THE
 * POSSIBILITY OF SUCH DAMAGES.
 */

/**
 * Admin HTML Config for feed attributes
 *
 * @author     SLI Systems
 */
namespace SLI\Feed\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\BlockInterface;
use SLI\Feed\Block\Adminhtml\Form\Field\FeedAttributesBlock;

class FeedAttributesConfig extends AbstractFieldArray
{

    /**
     * @var FeedAttributesBlock $_feedAttributeRenderer
     */
    protected $_feedAttributeRenderer;


    /**
     * Retrieve attribute column renderer
     *
     * @return BlockInterface|FeedAttributesBlock
     */
    protected function _getAttributeRenderer()
    {
        if (!$this->_feedAttributeRenderer) {
            $this->_feedAttributeRenderer = $this->getLayout()->createBlock(
                'SLI\Feed\Block\Adminhtml\Form\Field\FeedAttributesBlock',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_feedAttributeRenderer->setClass('feed_attribute_select');
            $this->_feedAttributeRenderer->setTitle('Feed attributes available');
        }

        return $this->_feedAttributeRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'attribute_code',
            ['label' => __('Feed Attributes'), 'renderer' => $this->_getAttributeRenderer()]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Attribute');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getAttributeRenderer()->calcOptionHash($row->getData('attribute_code'))] =
            'selected="selected"';

        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );

    }
}