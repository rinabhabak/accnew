<?php
namespace Int\BdmManagement\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Model\GroupFactory;

class InstallData implements InstallDataInterface
{
    protected $groupFactory;
    
    public function __construct(GroupFactory $groupFactory) {
        $this->groupFactory = $groupFactory;
    }
    
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        
        $setup->startSetup();
        $group = $this->groupFactory->create();
        $group->setCode('BDM')
                ->setTaxClassId(3)
                ->save();
                
        $group = $this->groupFactory->create();   
        $group->setCode('BDM Manager')
                ->setTaxClassId(3)
                ->save();
                
        $setup->endSetup();
    }
}