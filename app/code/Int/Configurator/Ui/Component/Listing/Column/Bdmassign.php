<?php
namespace Int\Configurator\Ui\Component\Listing\Column;
 

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Customer\Model\Customer;
 
class Bdmassign extends Column
{
    
    protected $_searchCriteria;
	protected $_customers;
    public function __construct(
	ContextInterface $context,
	UiComponentFactory $uiComponentFactory,
	Customer $_customers,
	SearchCriteriaBuilder $criteria,
        array $components = [], array $data = [])
    {
        
        $this->_searchCriteria  = $criteria;
		 $this->_customers = $_customers;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
				$confid = $item['configurator_id'];
                $item[$this->getData('name')] = '<a href="javascript:void(0)" class="bdm-assign" confid="'.$confid.'">Assign</a>';
            }
        }
        return $dataSource;
    }
}