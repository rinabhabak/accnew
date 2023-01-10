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
echo '<pre>';
 $file = fopen('withouturl.csv', 'r');
 while (($line = fgetcsv($file)) !== FALSE) {
   //$line is an array of the csv elements
   print_r($line);
 }
 fclose($file);