<?php
/**
 * Alpine_Cms
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Denis Furman <denis.furman@alpineinc.com>
 */

namespace Alpine\Cms\Setup;

use Alpine\Cms\Model\CmsSetup;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData.
 *
 * @package Magazijnshopper\Cms\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * SetupCms.
     *
     * @var CmsSetup
     */
    protected $setupCMS;

    /**
     * InstallData constructor.
     *
     * @param CmsSetup $setupCMS
     */
    public function __construct(
        CmsSetup $setupCMS
    ) {
        $this->setupCMS = $setupCMS;
    }

    /**
     * {@inheritdoc}
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $cmsBlocks = [
            'Alpine_Cms::Setup/include/cms_block_product.csv',
        ];

        foreach ($cmsBlocks as $blockId) {
            $this->setupCMS->loadAndInstallBlock($blockId);
        }

        $setup->endSetup();
    }
}
