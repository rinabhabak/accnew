<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Block\Adminhtml\Pagslider\Edit;
 
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
        $this->setId('pag_slider_form');
        $this->setTitle(__('Home Paginate Slider Information'));
    }
 
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Int\Homebanner\Model\Owlslider $model */
        $model = $this->_coreRegistry->registry('pag_home_banner');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post','enctype' => 'multipart/form-data']]
        );
 
        $form->setHtmlIdPrefix('homepagebanner_');
 
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );
 
        if ($model->getId()) {
            $fieldset->addField('slider_id', 'hidden', ['name' => 'slider_id']);
        }
 
        
        $fieldset->addField(
            'image_caption',
            'text',
            [
                'name' => 'image_caption',
                'label' => __('Image Caption'),
                'title' => __('Image Caption'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );	
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
            'category_name',
            'text',
            [
                'name' => 'category_name',
                'label' => __('Category Name'),
                'title' => __('Category Name'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'category_description',
            'text',
            [
                'name' => 'category_description',
                'label' => __('Category Description'),
                'title' => __('Category Description'),
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
}
