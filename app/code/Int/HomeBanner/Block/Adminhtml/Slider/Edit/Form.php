<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Block\Adminhtml\Slider\Edit;
 
use \Magento\Backend\Block\Widget\Form\Generic;
 
class Form extends Generic
{
 
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
 
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }
 
    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('hoem_slider_form');
        $this->setTitle(__('Home Slider Information'));
    }
 
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Int\Homebanner\Model\Slider $model */
        $model = $this->_coreRegistry->registry('home_banner');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post','enctype' => 'multipart/form-data']]
        );
 
        $form->setHtmlIdPrefix('homebanner_');
 
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );
 
        if ($model->getId()) {
            $fieldset->addField('slider_id', 'hidden', ['name' => 'slider_id']);
        }
 
        
        
        $fieldset->addField(
            'home_banner_image',
            'image',
            [
                'name' => 'home_banner_image',
                'label' => __('image'),
                'title' => __('image'),
                'disabled' => $isElementDisabled
            ]
            );
        $fieldset->addField(
            'link',
            'text',
            [
                'name' => 'link',
                'label' => __('Banner Link'),
                'title' => __('Banner Link'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );  
        $fieldset->addField(
            'target',
            'select',
            [
                'label' => __('Target'),
                'title' => __('Target'),
                'name' => 'target',
                'required' => true,
                'options' => $this->getTargetOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'image_type',
            'select',
            [
                'label' => __('Image Type'),
                'title' => __('Image Type'),
                'name' => 'image_type',
                'required' => true,
                'options' => $this->getImageType(),
                'disabled' => $isElementDisabled
            ]
        );
        
      
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
 
        return parent::_prepareForm();
    }
     protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    public function getTargetOptionArray(){
        return array(
                    '_self' => "Self",
                    '_blank' => "New Window",
                    );
    }

    public function getImageType(){
        return array(
                    '0' => "Mobile",
                    '1' => "Desktop",
                    );
    }
}