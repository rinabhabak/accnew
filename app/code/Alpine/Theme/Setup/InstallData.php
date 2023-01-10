<?php
/**
 * Alpine_Theme
 *
 * @category    Alpine
 * @package     Alpine_Theme
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Valery Shishkin <valery.shishkin@alpineinc.com>
 */

namespace Alpine\Theme\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use Magento\Theme\Model\Theme\Registration;

/**
 * Class InstallData
 *
 * @category    Alpine
 * @package     Alpine_Theme
 */
class InstallData implements InstallDataInterface
{
    const THEME_NAME = 'Alpine/accuride';

    /**
     * @var \Magento\Theme\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    private $collectionFactory;
    
    /**
     * Resource Model Config
     *
     * @var ResourceConfig
     */
    protected $resourceConfig;
    
    /**
     * Theme registration
     *
     * @var Registration
     */
    private $themeRegistration;

    /**
     * InstallData constructor.
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory
     * @param \Magento\Theme\Model\Config $config
     * @param Registration $themeRegistration
     */
    public function __construct(
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory,
        \Magento\Theme\Model\Config $config,
        Registration $themeRegistration
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        $this->themeRegistration = $themeRegistration;
    }

    /**
     * Install function
     * 
     * Assign theme to default store
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        
        $this->themeRegistration->register();

        $themes = $this->collectionFactory->create()->loadRegisteredThemes();
        
        /**
         * @var \Magento\Theme\Model\Theme $theme
         */
        foreach ($themes as $theme) {
            if ($theme->getCode() == self::THEME_NAME) {
                $this->config->assignToStore(
                    $theme,
                    [Store::DEFAULT_STORE_ID],
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );
            }
        }

        $setup->endSetup();
    }
}