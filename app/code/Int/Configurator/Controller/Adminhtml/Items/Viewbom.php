<?php
/**
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\Configurator\Controller\Adminhtml\Items;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Int\ConfiguratorPdf\Helper\Data as ConfiguratorPdfHelper;

class Viewbom extends \Magento\Backend\App\Action
{
	protected $layoutFactory;
	private $_configuratorDataProvider;
    protected $_configuratorFactory;
    protected $_fixtureFactory;
    protected $_openingTypesFactory;
    protected $_openingTypesCollectionFactory;
    protected $optionFactory;
	protected $_customerRepositoryInterface;
	protected $_status;
	protected $_productCollectionFactory;
	protected $_productObj;
	protected $_imgObj;
	protected $priceHelper;
    protected $_configuratorPdfHelper;
	protected $_configuratorProductHelper;
	protected $_directory;
	protected $_scopeConfig;   
    protected $_storeManager;
	private $_products;
    private $_fixtureId;
		
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
		\Int\Configurator\Model\ConfiguratorFactory $configuratorFactory,
		\Int\Configurator\Model\ResourceModel\Fixture\CollectionFactory $fixtureFactory,
		\Int\Configurator\Model\OpeningTypesFactory $openingTypesFactory,
		\Int\Configurator\Model\ResourceModel\OpeningTypes\CollectionFactory $openingTypesCollectionFactory,
		\Int\Configurator\Model\Status $status,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\ProductFactory $productObj,
		\Magento\Catalog\Helper\Image $imgObj,
		PriceHelper $priceHelper,
        ConfiguratorPdfHelper $configuratorPdfHelper,
		\Int\Configurator\Helper\Products $configuratorProductHelper,
		\Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
		\Magento\Framework\Filesystem\DirectoryList $directory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
		
    ) {
		$this->_customerRepositoryInterface = $customerRepositoryInterface;
		$this->_configuratorFactory = $configuratorFactory;
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_openingTypesFactory  = $openingTypesFactory;
        $this->_openingTypesCollectionFactory = $openingTypesCollectionFactory;
		$this->_status = $status;
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->_productObj = $productObj;
		$this->_imgObj = $imgObj;
		$this->priceHelper = $priceHelper;
        $this->_configuratorPdfHelper = $configuratorPdfHelper;
		$this->resultRawFactory      = $resultRawFactory;
        $this->fileFactory           = $fileFactory;
		$this->_directory = $directory;
		$this->_scopeConfig     = $scopeConfig;
		$this->_configuratorProductHelper = $configuratorProductHelper;
        parent::__construct($context);
		$this->_products = [];
        $this->_fixtureId = 0;
    }
  
    public function execute()
    {
		$configuratorId = $this->getRequest()->getParam('id');
		
		$configurator = $this->_configuratorFactory->create()->load($configuratorId);
        if(!$configurator->getId()){
            throw new \Exception('Invalid configurator.');
        }
      
      
		// Fetch customer details		
		$customerDetails = $this->getCustomerDetails($configurator->getCustomerId());
		
		$this->_products = $this->_configuratorProductHelper->getProductLists($configuratorId);

		
		$customerName = $customerDetails->getfirstname().' '.$customerDetails->getlastname();
		$pdfHtml = $this->getHtmlForPdf($configurator, $this->_products, $customerName, $customerDetails->getEmail(), $configurator->getCreatedAt());
				
		$pdf_link = $this->_configuratorPdfHelper->generatePdf($pdfHtml,$configurator->getId());        
		
		/**** save PDF link into configurator table ****/		
		$configurator = $this->_configuratorFactory->create()->load($configurator->getId());
		$configurator->setPdfLink($pdf_link);
		$configurator->save();
		
		$downloadedFileName = 'Bill_of_Material-'.$configurator->getId().'.pdf';
        $file = $this->_directory->getPath("media")."/configurator_bom/".$downloadedFileName;       
     
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="'.$downloadedFileName.'"');
		readfile($file);

		
    }
	
	public function getCustomerDetails($customerId) {
			$customer = $this->_customerRepositoryInterface->getById($customerId);
			return $customer;
	}
	
	public function getStatus($status_id) {
			$status = $this->_status->getOptionText($status_id);
			return $status;
	}
	
	public function getFixtureDetails($configurator_id) {
				
		$fixture = $this->_fixtureFactory->create()->addFieldToFilter('configurator_id',$configurator_id);
	
		return $fixture;
	}

	public function getOptionsDetails($fixture_id) {
		
		$fixture = $this->_openingTypesCollectionFactory->create()->addFieldToFilter('fixture_id',$fixture_id);
		return $fixture;
	}
	
	
	 public function getHtmlForPdf($configurator,$products,$name,$email,$date) {
		
        $data = array();
        $data['logo_url'] = $this->_configuratorProductHelper->getLogo();
        $data['system_logo_url'] = array();
        $systems = $this->_configuratorProductHelper->getSelectedSystems($configurator->getId());
        foreach($systems as $system){
            $data['system_logo_url'][] = $this->_configuratorProductHelper->getSystemLogo($system);
        }
        
        $data['products'] = $products;
        $data['customer_name'] = $name;
        $data['customer_email'] = $email;
        $data['date'] = $date;
        
        $layout = $this->_view->getLayout();
        return $layout->createBlock('\Int\Configurator\Block\Adminhtml\Bom')
                ->setData($data)
                ->setConfigurator($configurator)
                ->setTemplate('Int_Configurator::bom_pdf.phtml')
                ->toHtml();
        
    }
	
	
	
}