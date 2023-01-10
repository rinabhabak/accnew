<?php
namespace Int\AttributeSet\Setup;

use Magento\Eav\Setup\EavSetup; 
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;


class InstallData implements InstallDataInterface
{
 	private $eavSetupFactory;
	private $attributeSetFactory;
	private $attributeSet;
	private $categorySetupFactory;

   	public function __construct(EavSetupFactory $eavSetupFactory, AttributeSetFactory $attributeSetFactory, CategorySetupFactory $categorySetupFactory )
    	{
        	$this->eavSetupFactory = $eavSetupFactory; 
        	$this->attributeSetFactory = $attributeSetFactory; 
        	$this->categorySetupFactory = $categorySetupFactory; 
    	} 
	
 	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
 	{
		$setup->startSetup();
	
        // CREATE ATTRIBUTE SET 
  		$categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
      
		$attributeSet = $this->attributeSetFactory->create();
		$entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
		$attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
		$data = [
			'attribute_set_name' => 'Senseon', 
			'entity_type_id' => $entityTypeId,
			'sort_order' => 200,
		];
		$attributeSet->setData($data);
		$attributeSet->validate();
		$attributeSet->save();
		$attributeSet->initFromSkeleton($attributeSetId);
		
		$attributeSet->save();
		
		$setup->endSetup();
	}
	
} ?>