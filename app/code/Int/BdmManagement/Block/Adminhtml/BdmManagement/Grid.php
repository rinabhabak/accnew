<?php
namespace Int\BdmManagement\Block\Adminhtml\BdmManagement;

/**
 * Adminhtml BdmManagement grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Int\BdmManagement\Model\ResourceModel\BdmManagement\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Int\BdmManagement\Model\BdmManagement
     */
    protected $_bdmmanagement;
    
    
    /**
     * @var \Int\BdmManagement\Model\Status
     */
    protected $_status;
    
    /**
     * @var \Int\BdmManagement\Model\CustomerGroup
     */
    protected $_customerGroup;
    

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Int\BdmManagement\Model\BdmManagement $bdmmanagementPage
     * @param \Int\BdmManagement\Model\ResourceModel\BdmManagement\CollectionFactory $collectionFactory
     * @param \Magento\Core\Model\PageLayout\Config\Builder $pageLayoutBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\Customer $bdmmanagement,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Int\BdmManagement\Model\Status $statusModel,
        \Int\BdmManagement\Model\CustomerGroup $customerGroup,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_bdmmanagement = $bdmmanagement;
        $this->_status = $statusModel;
        $this->_customerGroup = $customerGroup;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('bdmmanagementGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        
        $customerGroupIds = $this->_customerGroup->getCustomerGroupIds();
        
        $collection = $this->_collectionFactory->create();
        /* @var $collection \Int\BdmManagement\Model\ResourceModel\BdmManagement\Collection */
        
        $collection->addAttributeToFilter('group_id',$customerGroupIds);
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        
        $this->addColumn('entity_id', [
            'header'    => __('ID'),
            'index'     => 'entity_id',
        ]);
        
        $this->addColumn('firstname', ['header' => __('Firstname'), 'index' => 'firstname']);
        $this->addColumn('lastname', ['header' => __('Lastname'), 'index' => 'lastname']);
        
        $this->addColumn('email', ['header' => __('Email'), 'index' => 'email']);
        
        
        //$this->addColumn(
        //    'status',
        //    [
        //        'header' => __('Status'),
        //        'index' => 'status',
        //        'type' => 'options',
        //        'name'=>'status',
        //        'options' => $this->_status->getOptionArray()
        //    ]
        //);
        
        
        
        $this->addColumn(
            'group_id',
            [
                'header' => __('Group'),
                'index' => 'group_id',
                'type' => 'options',
                'name'=>'group_id',
                'options' => $this->_customerGroup->getOptionArray()
            ]
        );
        
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'type' => 'datetime',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );
        
        
        $this->addColumn(
            'updated_at',
            [
                'header' => __('Updated At'),
                'index' => 'updated_at',
                'type' => 'date',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );
        
        
        $this->addColumn(
            'action',
            [
                'header' => __('Edit'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit',
                            'params' => ['store' => $this->getRequest()->getParam('store')]
                        ],
                        'field' => 'entity_id'
                    ]
                ],
                'sortable' => false,
                'filter' => false,
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['entity_id' => $row->getId()]);
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
    
    
    
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_ids');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massdelete'),
                'confirm' => __('Are you sure?'),
            ]
        );


        return $this;
    }
    
}
