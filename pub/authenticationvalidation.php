<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$obj->get('\Magento\Framework\App\State')->setAreaCode('frontend');

if(isset($_POST) && isset($_POST['TransactionId'])):
    if($_POST['TransactionId'] != ''):
        $helper = $obj->get('Int\CyberSource\Helper\Data');
        header("Location: ".$helper->getConfig('payment/int_cybersource/return_url')."?TransactionId=".$_POST['TransactionId']);
        exit;
    endif; 
endif;
?>