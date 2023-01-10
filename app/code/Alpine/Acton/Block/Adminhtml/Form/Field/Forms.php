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

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use VladimirPopov\WebForms\Model\ResourceModel\Form\CollectionFactory;

/**
 * Alpine\Acton\Block\Adminhtml\Form\Field\Forms
 *
 * @category    Alpine
 * @package     Alpine_Acton
 */
class Forms extends Select
{
    /**
     * Web forms collection factory
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getFormOptions());
        }
        
        return parent::_toHtml();
    }
    
    /**
     * Get form options
     *
     * @return array
     */
    protected function getFormOptions()
    {
        $result = [];
        $collection = $this->collectionFactory->create();
        foreach ($collection as $form) {
            $result[] = [
                'value' => $form->getId(),
                'label' => $form->getName() . ' (' . $form->getCode() . ')'
            ];
        }
        
        return $result;
    }
    
    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
