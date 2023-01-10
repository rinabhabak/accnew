<?php
include('../../app/bootstrap.php');
use \Magento\Framework\App\Bootstrap;

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$eavSetupFactory = $objectManager->get('\Magento\Eav\Setup\EavSetupFactory');
$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$attributeFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Eav\Attribute');

$attributes =  array(
				"drawer_opening_spec_sidespace" => array(0.5),
				"drawer_lock_function" => array("auto-open","touch-release","regular","easy-close"),
				"drawer_locking_solution" => array("slide_integrated","standalone _lock"),
				"type_of_opening_drawer" => array("locking_drawer"),
				"account_number" => array("13500000","13300000","13100000","13510000"),
				"subaccount" => array(0),
				"project_number" => array(0),
				"slide_series" => array("6055","6050"),
				"cost_center" => array("80","99","71"),
				"uom" => array("pair","kit"),
				"type_of_opening_door" => array("hinged_cabinet_door","sliding_cabinet_door"),
				"hinged_door_lock_function" => array("easy-close","touch-release","auto-open"),
				"door_hinge_type" => array("free_swinging","easy-close"),
			   );

foreach($attributes as $attr_key => $attribute_arr) {
	
	$attributeInfo = $attributeFactory->getCollection()
	               ->addFieldToFilter('attribute_code',['eq'=>$attr_key])
	               ->getFirstItem();
	$attribute_id = $attributeInfo->getAttributeId();
	
	if(!$attribute_id){
		continue;
	}
	
	$option = array();
	$option['attribute_id'] = $attributeInfo->getAttributeId();
	$i = 0;
	foreach($attribute_arr as $key => $value){
		$j = "a".$i;
	    $option['value'][$j][0] = $value;
	    $i++;
	}
	 
	$eavSetup = $eavSetupFactory->create();
	$eavSetup->addAttributeOption($option);
	
	echo "Attribute $attr_key values updated <br />";
	
}