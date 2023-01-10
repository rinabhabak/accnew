<?php
/**
 * Alpine_Install
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Denis Furman <denis.furman@alpineinc.com>
 */

namespace Alpine\Install\Setup;

use Magento\Framework\App\Config\Storage\WriterInterface;
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
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * InstallData constructor.
     *
     * @param WriterInterface $configWriter
     */
    public function __construct(
        WriterInterface $configWriter
    ) {
        $this->configWriter = $configWriter;
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

        $this->configWriter->save(
            'design/footer/copyright',
            '©2018 ACCURIDE INTERNATIONAL INC.',
            'default',
            $scopeId = 0
        );

        $setup->endSetup();
    }
}
