<?php
/**
 * Ftp config class loads custom credentials from magento System Configuration.
 *
 * @category    Alpine
 * @package     Alpine_Cogs
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Cogs\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;

/**
 * FTP client with credentials from magento System Configuration
 *
 * @category    Alpine
 * @package     Alpine_Cogs
 */
class FtpConfig
{
    /**
     * FTP Export configuration root path
     *
     * @var string
     */
    const FTP_EXPORT_ROOT = 'alpine_cogs/credentials/';

    /**
     * FTP host name
     *
     * @var string
     */
    const FTP_EXPORT_HOSTNAME = self::FTP_EXPORT_ROOT . 'ftp_export_hostname';

    /**
     * FTP host port
     *
     * @var string
     */
    const FTP_EXPORT_PORT = self::FTP_EXPORT_ROOT . 'ftp_export_port';

    /**
     * FTP user name
     *
     * @var string
     */
    const FTP_EXPORT_USERNAME = self::FTP_EXPORT_ROOT . 'ftp_export_username';

    /**
     * FTP password
     *
     * @var string
     */
    const FTP_EXPORT_PASSWORD = self::FTP_EXPORT_ROOT . 'ftp_export_password';

    /**
     * FTP Export path source
     *
     * @var string
     */
    const FTP_EXPORT_PATH = self::FTP_EXPORT_ROOT . 'ftp_export_path';

    /**
     * FTP Export path processed
     *
     * @var string
     */
    const FTP_EXPORT_PATH_PROCESSED = self::FTP_EXPORT_ROOT . 'ftp_export_path_processed';

    /**
     * Map between system configuration parameter names and configuration paths
     *
     * @var array
     */
    protected $configParameters = [
        'host'                  => self::FTP_EXPORT_HOSTNAME,
        'port'                  => self::FTP_EXPORT_PORT,
        'user'                  => self::FTP_EXPORT_USERNAME,
        'password'              => self::FTP_EXPORT_PASSWORD,
        'export_path'           => self::FTP_EXPORT_PATH,
        'export_path_processed' => self::FTP_EXPORT_PATH_PROCESSED
    ];

    /**
     * Scope to access system configuration
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * FtpExport constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Loads system configuration to open ftp connection with credentials from magento System Configuration
     * Possible argument keys (all keys are optional: current default keys will be loaded from System Configuration):
     * - host        default ftp.accuride.com
     * - port        default 21
     * - user        default magento
     * - password    default empty: loaded from System Configuration
     * - ssl         default false
     * - passive     default true
     * - export_path default var/export/
     * - export_path_processed default var/export/processed
     *
     * Keys from function parameter $configParameters and loaded from system configuration are merged:
     * Parameter keys $configParameters has more priority, than keys loaded from magento System Configuration
     *
     * @param array $configParameters
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getFtpConfigs(array $configParameters = [])
    {
        foreach ($this->configParameters as $configParameter => $configPath) {
            if ( ! array_key_exists($configParameter, $configParameters)) {
                $configParameters[$configParameter] = $this->getValue($configPath);
            }
        }
        if ( ! array_key_exists('ssl', $configParameters)) {
            $configParameters['ssl'] = false;
        }
        if ( ! array_key_exists('passive', $configParameters)) {
            $configParameters['passive'] = true;
        }

        return $configParameters;
    }

    /**
     * Get any configuration value
     *
     * @param string $configPath
     * @param string $scope
     * @return mixed
     */
    public function getValue($configPath, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($configPath, $scope);
    }
}