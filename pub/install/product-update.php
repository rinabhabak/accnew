<?php

// MAGENTO START
include('../../app/bootstrap.php');

use Magento\Framework\App\Bootstrap;

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
$storeIds = array_keys($storeManager->getStores());
$action = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Action');

//$productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
//$collection = $productCollectionFactory->create();
////$collection->addAttributeToFilter('sku', '24-MB01');
//$collection->addAttributeToSelect('*');
//foreach ($collection as $product) 
//{
//    foreach ($storeIds as $storeId) {
//        
//        $updateAttributes['meta_title'] = $product->getName();        
//        $action->updateAttributes([$product->getId()], $updateAttributes, $storeId);
//    }
//}
/*

$file = fopen("/var/www/html/var/import/catalog_product_20210210_114438.csv","r");


$i = 0;
$flag = 0;
while (($row = fgetcsv($file, 10000, ",")) !== FALSE) {

	if($flag==0){
		$flag = 1;
		continue;
		
    }
    
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $productObject = $objectManager->get('Magento\Catalog\Model\Product');
    $product = $productObject->loadByAttribute('sku', $row[0]);
    if($product){
        $product->setName($row[1])->save();
        echo $row[0] .'=>' .$row[1]."\n";
    }else{
        echo $row[0] ." not found\n";
    }

}
*/


/*

$file = fopen("/var/www/html/var/import/uom.csv","r");
$i = 0;
$flag = 0;
while (($row = fgetcsv($file, 10000, ",")) !== FALSE) {

	if($flag==0){
		$flag = 1;
		continue;
		
    }
    
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $productObject = $objectManager->get('Magento\Catalog\Model\Product');
    $product = $productObject->loadByAttribute('sku', $row[0]);
    if($product){
        $product->setUom($row[1])->save();
        echo $row[0] .'=>' .$row[1]."\n";
    }else{
        echo $row[0] ." not found\n";
    }
}

*/
      $objectManager1 = \Magento\Framework\App\ObjectManager::getInstance();

$file = fopen("/var/www/html/var/import/aaa.csv","r");
$i = 0;
$flag = 0;
while (($row = fgetcsv($file, 10000, ",")) !== FALSE) {

	if($flag==0){
		$flag = 1;
		continue;
		
    }
    
    
    $productObject = $objectManager->get('Magento\Catalog\Model\Product');
    $product = $productObject->loadByAttribute('sku', $row[0]);
    if($product){
         echo "====================================================================\n\n";
        echo $product->getId()."\n\n";
        $_productRep = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        $_product = $_productRep->getById($product->getId());
        $_product->setOpeningSpecLengthDrawerBoxMax($row[1]);
        $_product->setOpeningSpecLengthDrawerBoxMin($row[2]);
        $_productRep->save($_product);
                //
                
  
        $_produc = $objectManager1->get('Magento\Catalog\Model\Product')->load($product->getId());
        //echo $row[0] .'=>' .$row[1].'=>'.$row[2]."\n";
        echo $_produc->getData('opening_spec_length_drawer_box_max') .'=>' .$_produc->getData('opening_spec_length_drawer_box_min')."\n";
    }else{
        echo $row[0] ." not found\n";
    }
    $product  = NULL;
    $_product = NULL;
}


$file = fopen("/var/www/html/var/import/locking-solution-exclude.csv","r");
$i = 0;
$flag = 0;
while (($row = fgetcsv($file, 10000, ",")) !== FALSE) {

	if($flag==0){
		$flag = 1;
		continue;
		
    }
    
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $productObject = $objectManager->get('Magento\Catalog\Model\Product');
    $product = $productObject->loadByAttribute('sku', $row[0]);
    if($product){
        $product->setExcludeFromUndermountAndSi($row[1])->save();
        echo $row[0] .'=>' .$row[1]."\n";
    }else{
        echo $row[0] ." not found\n";
    }
}

//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//$productObject = $objectManager->get('Magento\Catalog\Model\Product');
//$product = $productObject->loadByAttribute('sku', 'D38EL-AO24P');
//$product->setData('drawer_lock_function','Regular')->save();
