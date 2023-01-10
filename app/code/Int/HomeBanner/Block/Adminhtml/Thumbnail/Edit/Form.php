<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Block\Adminhtml\Thumbnail\Edit;
 
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
        $this->setId('home_thumbnail_form');
        $this->setTitle(__('Home Thumbnail Information'));
    }
 
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Int\Homebanner\Model\Thumbnail $model */
        $model = $this->_coreRegistry->registry('home_banner');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form', 
                'action' => $this->getData('action'), 
                'method' => 'post',
                'enctype' => 'multipart/form-data'
                ]
            ]
        );
 
        $form->setHtmlIdPrefix('home_thumbnail_');
 
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('General Information'), 
                'class' => 'fieldset-wide'
            ]
        );
 
        if ($model->getId()) {
            $fieldset->addField('thumbnail_id', 'hidden', ['name' => 'thumbnail_id']);
        }

        $fieldset->addField(
            'thumbnail_title',
            'text',
            [
                'name' => 'thumbnail_title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        
		$fieldset->addField(
    		'thumbnail_image',
    		'image',
    		[
	    		'name' => 'thumbnail_image',
	    		'label' => __('Thumbnail Image'),
                'title' => __('Thumbnail Image'),
                'required' => true,
	    		'disabled' => $isElementDisabled
    		]
        );

        $fieldset->addField(
            'thumbnail_link',
            'text',
            [
                'name' => 'thumbnail_link',
                'label' => __('External Link'),
                'title' => __('External Link'),
                'required' => true,
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
                'options' => array(
                    '_self' => "Self",
                    '_blank' => "New Window"
                ),
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
}
