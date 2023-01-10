<?php
/**
 * Alpine_Acton
 *
 * @category    Alpine
 * @package     Alpine_Acton
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */

namespace Alpine\Acton\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\DataObject;
use Alpine\Acton\Block\Adminhtml\Form\Field\Forms;

/**
 * Alpine\Acton\Block\Adminhtml\Form\Field\Mapping
 *
 * @category    Alpine
 * @package     Alpine_Acton
 */
class Mapping extends AbstractFieldArray
{
    /**
     * Form select renderer
     *
     * @var AbstractBlock
     */
    protected $formRenderer = null;

    /**
     * Returns renderer for form element
     *
     * @return AbstractBlock
     */
    protected function getFormRenderer()
    {
        if (!$this->formRenderer) {
            $this->formRenderer = $this->getLayout()->createBlock(
                Forms::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->formRenderer;
    }
    
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'webform_id',
            [
                'label'     => __('Webform Code'),
                'renderer'  => $this->getFormRenderer(),
            ]
        );
        $this->addColumn(
            'form_post_url',
            [
                'label' => __('Form Post Url')
            ]
        );
        $this->_addAfter = false;
    }
    
    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $webform = $row->getWebformId();
        $options = [];
        if ($webform) {
            $options[
                'option_' . $this->getFormRenderer()->calcOptionHash($webform)
            ] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}
