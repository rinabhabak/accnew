<?php
/**
 * @category  Accuride
 * @package   Accuride_ProductAttachment
 * @copyright Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact   mail@sitewards.com
 */

namespace Accuride\ProductAttachment\Setup;

use Accuride\ProductAttachment\Setup\Service\MigrateFiles;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\DB\Adapter\AdapterInterface as DbAdapter;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    /** @var MigrateFiles */
    private $migrateFiles;

    /** @var State */
    private $state;

    /** @var array */
    private $tablesToClean = [
        'amasty_file'               => 'file_id',
        'amasty_file_store'         => 'file_store_id',
        'amasty_file_store_product' => 'file_store_product_id',
    ];

    /**
     * @param State        $state
     * @param MigrateFiles $migrateFiles
     */
    public function __construct(State $state, MigrateFiles $migrateFiles)
    {
        $this->state        = $state;
        $this->migrateFiles = $migrateFiles;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @throws \Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();
        $this->cleanNewTables($connection);

        $this->state->emulateAreaCode(
            Area::AREA_ADMINHTML,
            [$this, 'triggerDataMigration'],
            [$setup]
        );

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function triggerDataMigration(ModuleDataSetupInterface $setup)
    {
        $this->migrateFiles->execute($setup);
    }

    /**
     * @param DbAdapter $connection
     */
    private function cleanNewTables(DbAdapter $connection)
    {
        foreach ($this->tablesToClean as $table=>$primaryKey) {
            $connection->delete(
                $table,
                $primaryKey . ' > 0'
            );
        }
    }
}
