<?php
include('../../app/bootstrap.php');
use \Magento\Framework\App\Bootstrap;
$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();
$url = \Magento\Framework\App\ObjectManager::getInstance();

$state = $objectManager->get('\Magento\Framework\App\State');
$state->setAreaCode('frontend');



$rootNodeId = 92;
/// Get Root Category
$rootCat = $objectManager->get('Magento\Catalog\Model\Category');
$cat_info = $rootCat->load($rootNodeId);

$categoryName = "System";

$name=ucfirst($categoryName);
$url=strtolower($categoryName);
$cleanurl = trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($url))))));
$categoryFactory=$objectManager->get('\Magento\Catalog\Model\CategoryFactory');
/// Add a new sub category under root category
$categoryTmp = $categoryFactory->create();
$categoryTmp->setName($name);
$categoryTmp->setIsActive(true);
$categoryTmp->setUrlKey($cleanurl);
$categoryTmp->setParentId($rootCat->getId()); 
$categoryTmp->setPath($rootCat->getPath());
$categoryTmp->save();

echo " Category Created :".$categoryTmp->getId();

$subrootNodeId = $categoryTmp->getId();
$subrootCat = $objectManager->get('Magento\Catalog\Model\Category');
$subcat_info = $subrootCat->load($subrootNodeId);
/// Get Root Category

$categories = array('Senseon One','Shop','Senseon Core','Senseon Plus'); // Category Names
foreach($categories as $cat)
{
	$name=ucfirst($cat);
	$url=strtolower($cat);
	$cleanurl = trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($url))))));
	$categoryFactory=$objectManager->get('\Magento\Catalog\Model\CategoryFactory');
	/// Add a new sub category under root category
	$categoryTmp = $categoryFactory->create();
	$categoryTmp->setName($name);
	$categoryTmp->setIsActive(true);
	$categoryTmp->setUrlKey($cleanurl);
	$categoryTmp->setParentId($subrootCat->getId());
	$categoryTmp->setPath($subrootCat->getPath());
	$categoryTmp->save();
	
	echo " Category Created :".$categoryTmp->getName();
}