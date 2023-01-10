<?php
namespace Int\Configurator\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Customer\Model\Customer;
use Magento\Framework\UrlInterface;
 
class View extends Column
{  
    protected $_searchCriteria;
	protected $_customers;
	private $urlBuilder;
	/** Url Path */
    const PRODUCT_URL_PATH_EDIT = 'int_configurator/items/view';
    public function __construct(
	ContextInterface $context,
	UiComponentFactory $uiComponentFactory,
	UrlInterface $urlBuilder,
	Customer $_customers,
	SearchCriteriaBuilder $criteria,
        array $components = [], array $data = [])
    {
        
        $this->_searchCriteria  = $criteria;
		$this->_customers = $_customers;
		$this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
				$redirectUrl = $this->urlBuilder->getUrl(self::PRODUCT_URL_PATH_EDIT, ['id' => $item['configurator_id']]);
                $item[$this->getData('name')] = '<a class="view-details" target="_blank" href="'.$this->urlBuilder->getUrl(self::PRODUCT_URL_PATH_EDIT, ['id' => $item['configurator_id']]).'" >View</a>';
               
            }
        }
        return $dataSource;
    }
}