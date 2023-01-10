<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Setup;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('int_home_banner_slider')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('int_home_banner_slider')
				)
			->addColumn(
					'slider_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
					'identity' => true,
					'nullable' => false,
					'primary'  => true,
					'unsigned' => true,
					],
					'Slider ID'
					)
			->addColumn(
                    'link',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Link'
                	)
			->addColumn(
                    'target',
                    Table::TYPE_TEXT,
                    10,
                    ['nullable' => false],
                    'Target'
                	)  
            ->addColumn(
                    'is_active',
                    Table::TYPE_SMALLINT,
                    null,
                    [],
                    'Active Status'
                	)
			->addColumn(
					'home_banner_image',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Home Banner Image'
					)
			->addColumn(
            		'image_type',
            		Table::TYPE_BOOLEAN,
            		null,
            		[ 'identity' => false, 'nullable' => false, 'primary' => true ],
            		'Home Banner Image Type'
        	)
			->addColumn(
					'created_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Created At'
					)
			->addColumn(
					'updated_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
			->setComment('Int Home Banner Slider Table');
			$installer->getConnection()->createTable($table);
		}

		if (!$installer->tableExists('int_home_second_banner_slider')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('int_home_second_banner_slider')
				)
			->addColumn(
					'slider_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
					'identity' => true,
					'nullable' => false,
					'primary'  => true,
					'unsigned' => true,
					],
					'Slider ID'
					)
			->addColumn(
                    'link',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Link'
                	)
			->addColumn(
                    'target',
                    Table::TYPE_TEXT,
                    10,
                    ['nullable' => false],
                    'Target'
                	)  
            ->addColumn(
                    'is_active',
                    Table::TYPE_SMALLINT,
                    null,
                    [],
                    'Active Status'
                	)
			->addColumn(
					'home_banner_image',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Home Banner Image'
					)
			->addColumn(
                    'title',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Title'
                	)
			->addColumn(
					'created_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Created At'
					)
			->addColumn(
					'updated_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
			->setComment('Int Home Banner Slider Table');
			$installer->getConnection()->createTable($table);
		}

		if (!$installer->tableExists('int_home_pagination_slider')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('int_home_pagination_slider')
				)
			->addColumn(
					'slider_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
					'identity' => true,
					'nullable' => false,
					'primary'  => true,
					'unsigned' => true,
					],
					'Slider ID'
					)
			->addColumn(
                    'link',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Link'
                	)
			->addColumn(
                    'target',
                    Table::TYPE_TEXT,
                    10,
                    ['nullable' => false],
                    'Target'
                	)  
            ->addColumn(
                    'is_active',
                    Table::TYPE_SMALLINT,
                    null,
                    [],
                    'Active Status'
                	)
			->addColumn(
					'home_banner_image',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Home Bannerr Image'
					)
			->addColumn(
                    'image_caption',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Image Caption'
                	)
			->addColumn(
                    'category_name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Category Name'
                	)
			->addColumn(
                    'category_description',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Category Description'
                	)
			->addColumn(
					'created_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Created At'
					)
			->addColumn(
					'updated_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
			->setComment('Int Home Banner Slider Table');
			$installer->getConnection()->createTable($table);
		}

		if (!$installer->tableExists('int_home_thumbnail_slider')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('int_home_thumbnail_slider')
			)
			->addColumn(
				'thumbnail_id',
				Table::TYPE_INTEGER,
				null,
				[
				'identity' => true,
				'nullable' => false,
				'primary'  => true,
				'unsigned' => true,
				],
				'Thumbnail ID'
			)
			->addColumn(
				'thumbnail_image',
				Table::TYPE_TEXT,
				255,
				['nullable' => false],
				'Thumbnail Image'
			)
			->addColumn(
				'thumbnail_title',
				Table::TYPE_TEXT,
				255,
				['nullable' => false],
				'Thumbnail Title'
			)
			->addColumn(
				'thumbnail_link',
				Table::TYPE_TEXT,
				255,
				['nullable' => false],
				'Thumbnail Link'
			)
			->addColumn(
				'target',
				Table::TYPE_TEXT,
				10,
				['nullable' => false],
				'Thumbnail Target'
			)  
			->addColumn(
				'is_active',
				Table::TYPE_SMALLINT,
				null,
				[],
				'Active Status'
			)
			->addColumn(
				'created_at',
				Table::TYPE_TIMESTAMP,
				null,
				['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
				'Created At'
			)
			->addColumn(
				'updated_at',
				Table::TYPE_TIMESTAMP,
				null,
				['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
				'Updated At'
			)->setComment('Int Home Thumbnail Table');
			$installer->getConnection()->createTable($table);
		}
		$installer->endSetup();
	}
}