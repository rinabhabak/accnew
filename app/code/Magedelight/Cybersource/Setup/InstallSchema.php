<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Cybersource
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Cybersource\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\InstallSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('magedelight_cybersource');
        
        if ($setup->getConnection()->isTableExists($tableName) != true) 
        {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('magedelight_cybersource')
            )->addColumn(
                'card_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Card Id'
            )->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                '10',
                ['unsigned' => true, 'nullable' => true],
                'Customer Website Id'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                '10',
                ['unsigned' => true, 'nullable' => true],
                'Customer ID'
            )->addColumn(
                'subscription_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '30',
                [],
                'Customer Subscription Id'
            )->addColumn(
                'firstname',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                150,
                [],
                'Card Customer First Name'
            )
            ->addColumn(
                'lastname',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                150,
                [],
                'Card Customer Last Name'
            )
            ->addColumn(
                'postcode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [],
                'PostCode'
            )
            ->addColumn(
                'country_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                [],
                'Country ID'
            )
            ->addColumn(
                'region_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '150',
                [],
                'Region ID'
            )
            ->addColumn(
                'state',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '150',
                [],
                'State'
            )
            ->addColumn(
                'city',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '150',
                [],
                'CustomerCity'
            )
            ->addColumn(
                'company',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '150',
                [],
                'Customer Company'
            )
            ->addColumn(
                'street',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Customer Street'
            )
            ->addColumn(
                'telephone',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '50',
                [],
                'Customer Telephone'
            )
            ->addColumn(
                'cc_exp_month',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '12',
                [],
                'Card Exp Month'
            )
            ->addColumn(
                'cc_last_4',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '100',
                [],
                'Card Last Four Digit'
            )
            ->addColumn(
                'cc_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '32',
                [],
                'Card Type'
            )
            ->addColumn(
                'cc_exp_year',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '4',
                [],
                'Card Exp Year'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Card Creation Time'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Card Modification Time'
            )
            ->addForeignKey(// pri col name         //ref tabname     //ref cou name
                'CYBERSOURCE_CUSTOMER_ID',
                'customer_id', // table column name
                $installer->getTable('customer_entity'),   // ref table name
                'entity_id',   // ref column name
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL  // on delete
            )
            ;
            $installer->getConnection()->createTable($table);
        }    
        

        $connection = $installer->getConnection();

        $quotePayment = $installer->getTable('quote_payment');
        $quoteColumns = [];
        $quoteColumns1 = [
            'magedelight_cybersource_subscription_id' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '30',
                'nullable' => false,
                'comment' => 'Subscription Id',
            ],
        ];

        foreach ($quoteColumns1 as $name => $definition) {
            $connection->addColumn($quotePayment, $name, $definition);
        }

        $quoteColumns = [
            'magedelight_cybersource_requestid' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '30',
                'nullable' => false,
                'comment' => 'Request ID',
            ],
        ];

        foreach ($quoteColumns as $name => $definition) {
            $connection->addColumn($quotePayment, $name, $definition);
        }

        $orderPayment = $installer->getTable('sales_order_payment');

        $orderColumns1 = [
            'magedelight_cybersource_subscription_id' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '30',
                'nullable' => false,
                'comment' => 'Subscription Id',
            ],
        ];

        foreach ($orderColumns1 as $name => $definition) {
            $connection->addColumn($orderPayment, $name, $definition);
        }

        $orderColumns = [
            'magedelight_cybersource_requestid' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '30',
                'nullable' => false,
                'comment' => 'Subscription Id',
            ],
        ];

        foreach ($orderColumns as $name => $definition) {
            $connection->addColumn($orderPayment, $name, $definition);
        }

        $quoteToken = $installer->getConnection()->tableColumnExists($quotePayment, 'cybersource_token', '');

        if ($quoteToken == false) {
            $quoteColumns = [];
            $quoteColumns = [
                'cybersource_token' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    [],
                    'comment' => 'Cybersource Token',
                ],
            ];

            foreach ($quoteColumns as $name => $definition) {
                $connection->addColumn($quotePayment, $name, $definition);
            }
        }

        $orderToken = $installer->getConnection()->tableColumnExists($orderPayment, 'cybersource_token', '');

        if ($orderToken == false) {
            $orderColumns = [];

            $orderColumns = [
                'cybersource_token' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    [],
                    'comment' => 'Cybersource Token',
                ],
            ];

            foreach ($orderColumns as $name => $definition) {
                $connection->addColumn($orderPayment, $name, $definition);
            }
        }

        $invoiceToken = $installer->getConnection()->tableColumnExists($installer->getTable('sales_invoice'), 'cybersource_token', '');

        if ($invoiceToken == false) {
            $invoiceColumns = [];

            $invoiceColumns = [
                'cybersource_token' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    [],
                    'comment' => 'Cybersource Token',
                ],
            ];

            foreach ($invoiceColumns as $name => $definition) {
                $connection->addColumn($installer->getTable('sales_invoice'), $name, $definition);
            }
        }

        $creditToken = $installer->getConnection()->tableColumnExists($installer->getTable('sales_creditmemo'), 'cybersource_token', '');

        if ($creditToken == false) {
            $creditColumns = [];

            $creditColumns = [
                'cybersource_token' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    [],
                    'comment' => 'Cybersource Token',
                ],
            ];

            foreach ($creditColumns as $name => $definition) {
                $connection->addColumn($installer->getTable('sales_creditmemo'), $name, $definition);
            }
        }

        $installer->endSetup();
    }
}
