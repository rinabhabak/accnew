<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\ConfiguratorGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Int\ConfiguratorPdf\Helper\Data as ConfiguratorPdfHelper;

/**
 * Class Configurator
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class GenerateBom implements ResolverInterface
{

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
	protected $_scopeConfig; 
	protected $_directory;
    private $_products;
    private $_fixtureId;
    protected $_blockFactory;
    protected $_fixture;
    protected $_configuratorProductHelper;
    
    /**
     * @param Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Configurator $configuratorDataProvider
     */
    public function __construct(
        \Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Configurator $configuratorDataProvider,
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
        \Int\Configurator\Helper\Products $configuratorProductHelper,
		\Magento\Framework\Filesystem\DirectoryList $directory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Int\Configurator\Model\FixtureFactory $fixture,
        ConfiguratorPdfHelper $configuratorPdfHelper
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
		$this->_directory = $directory;
		$this->_scopeConfig     = $scopeConfig;
        $this->_configuratorPdfHelper = $configuratorPdfHelper;
        $this->_products = [];
        $this->_fixtureId = 0;
        $this->_blockFactory = $blockFactory;
        $this->_fixture = $fixture;
        $this->_configuratorProductHelper = $configuratorProductHelper;
        
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        
        $output = array();
        $configuratorId = $args['configurator_id'];
        $configurator = $this->_configuratorFactory->create()->load($configuratorId);
        if(!$configurator->getId()){
            throw new \Exception('Invalid configurator.');
        }
      
        
        $output = $configurator->getData();
       
		// Fetch customer details
		
		$customerDetails = $this->getCustomerDetails($configurator->getCustomerId());
		$fixtureDetails = $this->getFixtureDetails($configurator->getId());
		
        $this->_products = $this->_configuratorProductHelper->getProductLists($configuratorId);
		
		$customerName = $customerDetails->getfirstname().' '.$customerDetails->getlastname();
		$pdfHtml = $this->getHtmlForPdf($configurator, $this->_products, $customerName, $customerDetails->getEmail(), $configurator->getCreatedAt());
		
		$pdf_link = $this->_configuratorPdfHelper->generatePdf($pdfHtml,$configurator->getId());
        
		/**** save PDF link into configurator table ****/		
		$configurator = $this->_configuratorFactory->create()->load($configuratorId);
		$configurator->setPdfLink($pdf_link);
		$configurator->save();
		
		
		$output['configurator_id'] = $configurator->getId();
        $output['pdf_link'] = $pdf_link.'?hash='.time();
        
        return $output;
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
	
	public function getProductdetails($id) {
		
        return $this->_productObj->create()->load($id);
    }
	
	
    public function getHtmlForPdf($configurator,$products,$name,$email,$date) {
		
        
        ob_start();
            ?>
            
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <meta http-equiv='X-UA-Compatible' content='IE=edge'>
                <title>Bill of Material</title>
                <meta name='viewport' content='width=device-width, initial-scale=1'>
            </head>
            <body>
                <table style="width: 700px; margin: auto; font-family: sans-serif;">
                    <tr>
                        <td colspan="2" style="text-align: right; font-size: 40px; padding-bottom: 60px;">
                            <img src="<?php echo $this->_configuratorProductHelper->getLogo() ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-size: 40px; color: #333;">
                            Bill of Material
                        </td>
                        <td style="text-align: right; font-size: 18px;">
                            <p style="font-size: 22px; color: #333; margin: 0; padding: 0;"><?php echo __('Date: %1',$date); ?></p>
                            <p style="font-size: 22px; color: #333; margin: 0; padding: 0;"><?php echo __('ID Number: %1', $configurator->getProjectId()); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-size: 40px; vertical-align: bottom; color: #333; padding-top: 30px; padding-bottom: 30px;">
                            <p style="font-size: 25px; color: #222; margin: 0; padding: 0;">Senseon</p>
                            <p style="font-size: 18px; color: #333; margin: 0; padding: 0;">
                                12311 Shoemaker Avenue <br/>
                                Santa Fe Springs, CA 90670
                            </p>
                        </td>
                        <td style="text-align: left; font-size: 18px; vertical-align: bottom; padding-top: 30px; padding-bottom: 30px;">
                            <p style="font-size: 18px; color: #333; margin: 0; padding: 0;">To: <?php echo $name; ?></p>
                            <p style="font-size: 18px; color: #333; margin: 0; padding: 0;">Email: <?php echo $email; ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border-top: 1px #333 solid; font-size: 18px; color: #333;"> <p>Based on your input the following products are needed to secure your fixture(s):*</p> </td>
                    </tr>
                    <?php foreach($products as $fixtureId => $_products): ?>
                        <?php $fixture = $this->getFixture($fixtureId); ?>
                        <?php $system = $this->_configuratorProductHelper->getSelectedSystemsByFixtureId($fixtureId); ?>
                        <?php $flag = 0; ?>
                        <?php
                            $heightType = 0;
                            $widthType = 0;
                            $depthType = 0;
                            $height = $fixture->getFixtureHeight();
                            $depth = $fixture->getFixtureDepth();
                            $width = $fixture->getFixtureLength();
                        ?>
                        <?php
                            foreach($this->getOptionsDetails($fixtureId)->getData() as $opening){
                                $productData = unserialize($opening['product_data']);
                                $customFields = $productData['custom_fields_data'];                  
                                foreach($customFields as $customField){
                                    if($customField['field_name']=='height'){
                                        $heightType = $customField['field_value'];
                                    }
                                    
                                    if($customField['field_name']=='depth'){
                                        $depthType = $customField['field_value'];
                                    }
                                    
                                    if($customField['field_name']=='width'){
                                        $widthType = $customField['field_value'];
                                    }
                                    
                                }
                                if(($heightType > $height ) || ( $depthType > $depth ) || ($widthType > $width) ){
                                    $flag = 1;
                                    break;
                                }
                            }
                            
                        ?>
                        <?php $system = $this->_configuratorProductHelper->getSelectedSystemsByFixtureId($fixtureId); ?>
                        
                        <tr>
                            <td style="vertical-align: middle; line-height: 40px; padding-top: 20px; font-size: 18px; font-weight: 700; " ><?php echo $fixture->getFixtureName() ?></td>              
                        </tr>
                        <?php if($flag): ?>
                            <tr>
                                <td colspan="2" style="border-top: 1px #333 solid; font-size: 18px; color: #f00;"> <p>Your opening dimensions exceed your fixture’s dimensions. Your 3D preview and product recommendation may not be accurate as a result. Please call us for further consultation at 866-459-4149.</p> </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td> <img src="<?php echo $this->_configuratorProductHelper->getSystemLogo($system); ?>" alt="logo"  /> </td>  
                        </tr>
                        
                        
                        <tr>
                            <td colspan="2" style="margin: 0; padding: 0; padding-top: 15px;">
                                <table style="width: 700px; margin: auto; border: 1px #333 solid; border-collapse: collapse; margin-bottom: 40px;">
                                   
                                    <tr style="background: #fff; color: #000; padding: 10px;">
                                        <th style="border: 1px #333 solid; width: 150px; text-align: left; font-size: 20px; color: #000; font-weight: 300; padding: 5px; padding-left: 10px;"><?php echo __('Part#') ?></th>
                                        <th style="border: 1px #333 solid; font-size: 20px; color: #000; font-weight: 300; padding: 5px;"><?php echo __('Description') ?></th>
                                        <th style="border: 1px #333 solid; width: 80px; font-size: 20px; color: #000; font-weight: 300; padding: 5px;"><?php echo __('UOM') ?></th>
                                        <th style="border: 1px #333 solid; width: 80px; font-size: 20px; color: #000; text-align: center; font-weight: 300; padding: 5px;"><?php echo __('Qty') ?></th>
                                    </tr>
                                    <?php $i=0; ?>
                                    
                                        <?php  if(count($_products)>0): ?>
                                            <?php foreach($_products as $product): ?>
                                                <tr>
                                                    <td style="border: 1px #333 solid; width: 150px; padding: 10px;"><?php echo $product['sku'] ?></td>
                                                    <td style="border: 1px #333 solid; padding: 10px;"><?php echo $product['name'] ?></td>
                                                    <td style="border: 1px #333 solid; width: 80px; padding: 10px; text-align: center;"><?php echo $product["uom"] ?></td>
                                                    <td style="border: 1px #333 solid; width: 80px; padding: 10px; text-align: center;"><?php echo $product['qty'] ?></td>
                                                </tr>
                                                <?php $i++; ?>
                                            <?php endforeach;?>
                                        <?php else:?>
                                            <tr>
                                                <td colspan="4" style="border: 1px #333 solid; padding: 10px; text-align: center;" ><?php echo __('No record found.') ?></td>
                                            </tr>
                                        <?php endif;;?>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="2" style="font-size: 15px; color: #333; margin: 0; padding: 0; padding-top: 25px; line-height: 20px;">
                            *This configuration may require additional cabling and components. Our Senseon expert team will contact you to help you finalize your setup.
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 18px; color: #333; margin: 0; padding: 0; padding-top: 30px; padding-bottom: 30px; line-height: 25px;">
                            Thank you for building your System Configurator! Your project information has been sent to our team! They will reach out to you to confirm you have everything you need to secure your cabinet
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 18px; color: #333; margin: 0; padding: 0; padding-top: 30px; border-top: 1px #333 solid; line-height: 25px;">
                            All customer orders to Accuride are subject to written acceptance in the form of Accuride’s customer acknowledgement with general terms and conditions set forth on the back. Any additional or any conflicting terms and conditions set forth in the Buyer’s purchase order shall be null and void and shall not form part of any contract between Accuride and the Buyer.
                        </td>
                    </tr>
                </table>
				<!-- Add pagination into PFG -->
				<script type="text/php">
					if ( isset($pdf) ) {           
						$x = 550;
						$y = 750;
						$text = "{PAGE_NUM} of {PAGE_COUNT}";
						$font = $fontMetrics->get_font("helvetica", "normal");
						$size = 9;
						$color = array(000,0,0);
						$word_space = 0.0;  //  default
						$char_space = 0.0;  //  default
						$angle = 0.0;   //  default
						$pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
					}
				</script>
				<!-- End of pagination script -->
            </body>
            </html>
            
            <style>
                table {
                    page-break-inside: avoid !important;
                  }
            </style>
            <?php
            
        return ob_get_clean();
        
    }
    
    /* Get Label by option id */
    public function getOptionLabelByValue($attributeCode,$optionId)
    {
        $product = $this->_productObj->create();
        $isAttributeExist = $product->getResource()->getAttribute($attributeCode); 
        $optionText = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionText = $isAttributeExist->getSource()->getOptionText($optionId);
        }
        return $optionText;
    }
 
   /* Get Option id by Option Label */
    public function getOptionIdByLabel($attributeCode,$optionLabel)
    {
        $product = $this->_productObj->create();
        $isAttributeExist = $product->getResource()->getAttribute($attributeCode);
        $optionId = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionId = $isAttributeExist->getSource()->getOptionId($optionLabel);
        }
        return $optionId;
    }
	
    
    public function getFixture($fixtureId) {		
		$fixture = $this->_fixture->create()->load($fixtureId);      
		return $fixture;
	}
    
    
}