<?php
namespace Int\ProductUrlFixing\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	protected $_objectManager;

	protected $request;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
		\Magento\Framework\App\Request\Http $request
		)
	{
		$this->_pageFactory 	= $pageFactory;
		$this->_objectManager 	= $objectmanager;
		$this->request 			= $request;
		return parent::__construct($context);
	}

	public function execute()
	{

        $productCollection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');

		//$_product = $this->_objectManager->create('\Magento\Catalog\Model\Product');

		$collection = $productCollection->addAttributeToSelect('*')
					->load();
					$collection->addFieldToFilter(
			'url_key',
			array('null' => true)
		);
		$count 	= 0;
		$start 	= $this->getUrlParameter('start');
		$end 	= $this->getUrlParameter('end');
		echo 'product_id,sku,url_key,new_url_key<br>';
		foreach ($collection as $product){
			if($count >= $start && $count <= $end){
				$sku        = $product->getSku();
				$newUrlKey  = $this->slugify($product->getName(),$product->getSku());
				$_productId = $product->getId();
				$productLoaded = $this->_objectManager->create('\Magento\Catalog\Model\Product')->load($_productId);
				$productLoaded->setUrlKey($newUrlKey);
				$productLoaded->save();
				echo $product->getId().',';
				echo $product->getSku().',';
				echo $product->getUrlKey().',';
				echo $this->slugify($product->getName(),$product->getSku()).'<br>';
				$_productId = '';
				$newUrlKey  = '';
				$sku        = '';
			}			
			$count++;
		}
	}

	public function getUrlParameter($param)
    {
		$this->request->getParams(); 
		return $this->request->getParam($param);
    }
	public function slugify($name,$sku)
	{       
		//$skuUrlArray = explode ("-", $sku);		
		// if(count($skuUrlArray) > 1 && $skuUrlArray[1]){
		// 	$skuUrl = $skuUrlArray[1];
		// }else{
		// 	$skuUrl = $sku;
		// }

		$text = $sku.'-'.$name;
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = preg_replace('~[^-\w]+~', '', $text);
		$text = trim($text, '-');
		$text = trim(preg_replace('/\s*\([^)]*\)/', '', $text));
		$text = preg_replace('~-+~', '-', $text);
		$text = strtolower($text);
	
		if (empty($text)) {
			return 'n-a';
		}
	
	  	return $text;
	}
}