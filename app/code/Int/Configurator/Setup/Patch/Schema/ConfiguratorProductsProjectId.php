<?php
/**
* Copyright © 2019 Magenest. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Int\Configurator\Setup\Patch\Schema;


use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class ConfiguratorProductsProjectId implements SchemaPatchInterface
{
   private $moduleDataSetup;


   public function __construct(
       ModuleDataSetupInterface $moduleDataSetup
   ) {
       $this->moduleDataSetup = $moduleDataSetup;
   }


   public static function getDependencies()
   {
       return [];
   }


   public function getAliases()
   {
       return [];
   }


   public function apply()
   {
       $this->moduleDataSetup->startSetup();
    
        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('quote_item'),
            'configurator_pid',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment'  => 'Configurator Project Id',
            ]
        );

        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('sales_order_item'),
            'configurator_pid',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment'  => 'Configurator Project Id',
            ]
        );


       $this->moduleDataSetup->endSetup();
   }
}