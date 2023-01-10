<?php
namespace Int\BdmManagement\Block\Adminhtml\BdmManagement\Edit\Tab;

/**
 * Cms page edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Int\BdmManagement\Model\Status
     */
    protected $_statusModel;
    
    /**
     * @var \Int\BdmManagement\Model\CustomerGroup
     */
    protected $_customerGroup;
    
    
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
        \Int\BdmManagement\Model\Status $statusModel,
        \Int\BdmManagement\Model\CustomerGroup $customerGroup,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_statusModel = $statusModel;
        $this->_customerGroup = $customerGroup;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('bdmmanagement');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Int_BdmManagement::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('bdmmanagement_main_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }
        
        $fieldset->addField(
                'website_id',
                'select',
                [
                    'name' => 'website_id',
                    'label' => __('Associate to Website'),
                    'title' => __('Associate to Website'),
                    'required' => true,
                    'values' => $this->_systemStore->getWebsiteValuesForForm(),

            ]
        );
        
        
        $fieldset->addField(
                'group_id',
                'select',
                [
                    'name' => 'group_id',
                    'label' => __('Group'),
                    'title' => __('Group'),
                    'required' => true,
                    'values' => $this->_customerGroup->toOptionArray(),

            ]
        );
        

        $fieldset->addField(
            'firstname',
            'text',
            [
                'name' => 'firstname',
                'label' => __('Firstname'),
                'title' => __('Firstname'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        
        
        $fieldset->addField(
            'lastname',
            'text',
            [
                'name' => 'lastname',
                'label' => __('Lastname'),
                'title' => __('Lastname'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        
        
        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'title' => __('Email'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        
        $fieldset->addField(
            'phone_number',
            'text',
            [
                'name' => 'phone_number',
                'label' => __('Phone Number'),
                'title' => __('Phone Number'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        
        //$fieldset->addField('is_active', 'select', array(
        //    'label' => 'Status',
        //    'name' => 'is_active',           
        //    'values' => $this->_statusModel->toOptionArray()
        //));
        
        $this->_eventManager->dispatch('adminhtml_bdmmanagement_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('General Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
