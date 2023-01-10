<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$installer = $setup;

		$installer->startSetup();

		if(version_compare($context->getVersion(), '1.2.0', '<')) {
			$installer->getConnection()->addColumn(
				$installer->getTable( 'int_home_banner_slider' ),
				'image_type',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
					null,
					'nullable' => false,
					'comment' => 'Home Banner Image Type',
					'after' => 'home_banner_image'
				]
			);
		}



		$installer->endSetup();
	}
}
?>