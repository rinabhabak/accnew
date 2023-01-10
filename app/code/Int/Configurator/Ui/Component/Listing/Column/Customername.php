<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */
namespace Int\Configurator\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Customer\Model\Customer;
 
class Customername extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;
	protected $_customers;
    public function __construct(
	ContextInterface $context,
	UiComponentFactory $uiComponentFactory,
	OrderRepositoryInterface $orderRepository,
	Customer $_customers,
	SearchCriteriaBuilder $criteria,
        array $components = [], array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
		 $this->_customers = $_customers;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $customerId  = $item["customer_id"];
				$customer = $this->_customers->load($customerId);				
                $customerName = $customer->getName();
                $item[$this->getData('name')] = $customerName;
            }
        }
        return $dataSource;
    }
}