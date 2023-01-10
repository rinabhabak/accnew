<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '5G');
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;
require '../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');

$_product = $objectManager->create('\Magento\Catalog\Model\Product');

/** Apply filters here */
$collection = $productCollection->addAttributeToSelect('*')
            ->load();
            $collection->addFieldToFilter(
    'url_key',
    array('null' => true)
);
//echo '<pre>';
 //$fp = fopen('php://output', 'wb');
//echo count($collection);
$count = 0;

echo 'product_id,sku,url_key,new_url_key<br>';
foreach ($collection as $product){
    
    //print_r($product->getData());
    //if($product->getUrlKey() == null || $product->getUrlKey() == '' ){
        $sku        = $product->getSku();
        $newUrlKey  = slugify($product->getName(),$product->getSku());

        $productLoaded = $_product->load($product->getId());
        $productLoaded->setUrlKey($newUrlKey);
        $productLoaded->save();
        echo $product->getId().',';
        echo $product->getSku().',';
        echo $product->getUrlKey().',';
        echo slugify($product->getName(),$product->getSku()).'<br>';
        $count++;
        //die();
    //}
    // echo 'Product Id : '.$product->getId().'<br>';
    // //echo $product->getUrlKey().'<br>';
    // echo 'Generated url_key : '.slugify($product->getName(),$product->getSku()).'<br><br>';
    // $sku = $product->getSku();
    // $newUrlKey = slugify($product->getName(),$product->getSku());

    // // $prod = $_product->loadByAttribute('sku', $sku);
    // // $prod->setUrlKey($newUrlKey); // name of your custom attribute
    // // $prod->save();

    // // $productLoaded = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
    // // $productLoaded->setUrlKey($newUrlKey);
    // // $productLoaded->save();
    // //die();

}
echo 'Total::'.$count;
function slugify($name,$sku)
{       
    $skuUrlArray = explode ("-", $sku);
    
    if(count($skuUrlArray) > 1 && $skuUrlArray[1]){
        $skuUrl = $skuUrlArray[1];
    }else{
        $skuUrl = $sku;
    }
    $text = $name.'-'.$skuUrl;
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, '-');

    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);

    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

  return $text;
}