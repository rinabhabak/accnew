<?php
/**
 * Ftp class extends core magento framework Ftp class and loads custom credentials from magento System Configuration.
 *
 * Sample process of FTP Connection:
 * 1. Load configuration: by function $args = getFtpConfigs().
 * 2. Open a connection: by function $open = open($args), $args pass from 1.
 *    if ($open) { do 3. 4. }
 * 3. Write a file from string, file or stream: by function write().
 * 4. Close a connection: by function close().
 *
 * @category    Alpine
 * @package     Alpine_Cogs
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Cogs\Model\Filesystem\Io;

use Magento\Framework\Filesystem\Io\Ftp as FtpMagento;
use Alpine\Cogs\Helper\FtpConfig;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Exception\LocalizedException;
use \Psr\Log\LoggerInterface;

/**
 * FTP client with credentials from magento System Configuration
 *
 * @category    Alpine
 * @package     Alpine_Cogs
 */
class Ftp extends FtpMagento
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var File
     */
    protected $filesystemIo;

    /**
     * FTP system configuration
     *
     * @var FtpConfig
     */
    protected $ftpConfig;

    /**
     * FtpExport constructor
     *
     * @param LoggerInterface $logger
     * @param FtpConfig $ftpConfig
     * @param File $filesystemIo
     */
    public function __construct(
        LoggerInterface $logger,
        FtpConfig $ftpConfig,
        File $filesystemIo
    ) {
        $this->logger       = $logger;
        $this->ftpConfig    = $ftpConfig;
        $this->filesystemIo = $filesystemIo;
    }

    /**
     * Loads system configuration to open ftp connection with credentials from magento System Configuration
     * Possible argument keys (all keys are optional: current default keys will be loaded from System Configuration):
     * - host        default ftp.accuride.com
     * - port        default 21
     * - timeout     default 90
     * - user        default magento
     * - password    default empty: loaded from System Configuration
     * - ssl         default true
     * - passive     default true
     * - path        default var/export/
     * - file_mode   default FTP_BINARY
     *
     * if success return boolean true
     * if fails return string as error message
     *
     * @param array $args
     * @return true
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function open(array $args = [])
    {
        $args = $this->ftpConfig->getFtpConfigs($args);

        return parent::open($args);
    }

    /**
     * Get export path
     *
     * @return string
     */
    public function getExportPath()
    {
        return $this->ftpConfig->getValue($this->ftpConfig::FTP_EXPORT_PATH);
    }

    /**
     * Get export path for already processed files
     *
     * @return string
     */
    public function getExportPathProcessed()
    {
        return $this->ftpConfig->getValue($this->ftpConfig::FTP_EXPORT_PATH_PROCESSED);
    }

    /**
     * FTP Export of several files with file names passed as parameter $fileNames
     * FTP credentials and paths are loaded from magento system configuration
     *
     * @param array $fileNames File Names to FTP transfer
     * @param array $args Like core Magento Framework $args for Magento\Framework\Filesystem\Io\Ftp:open() function
     * @param bool $createProcessedPath Check if path processed exists, and create if not exists
     * @param bool $moveToProcessedPath Move processed file from source to processed Path
     * @return bool
     * @throws \Exception
     */
    public function export(array $fileNames, array $args = [], $createProcessedPath = true, $moveToProcessedPath = true)
    {
        $result = true;
        try {
            $this->logger->info('FTP: try to open.');
            $openResult = $this->open($args);
        } catch (LocalizedException $ex) {
            $this->logger->error('FTP: open error: ' . $ex->getMessage());

            return false;
        }

        /** @var true|string $openResult */
        if ($openResult !== true) {
            $this->logger->info('FTP: open error: result == false');

            return false;
        }

        $this->logger->info('FTP: opened OK.');

        foreach ($fileNames as $filename) {
            $src = $this->getExportPath() . $filename;

            $ftpResult = false;
            if ($this->filesystemIo->fileExists($src)) {
                $ftpResult = $this->write($filename, $src);
            } else {
                $this->logger->info('FTP: File does not exists: ' . $src);
            }

            if ($ftpResult && $moveToProcessedPath) {
                // Move local file from $src to processed path $dst
                $pathProcessed = $this->getExportPathProcessed();
                $dst           = $pathProcessed . $filename;

                // Check if path processed $pathProcessed exists, and create if not exists
                try {
                    if ($createProcessedPath) {
                        $this->filesystemIo->checkAndCreateFolder($pathProcessed);
                    }
                } catch (\Exception $ex) {
                    $this->logger->error('FTP: check if path processed exists: ' . $ex->getMessage());

                    return false;
                }

                // Move file from $src to $dst
                if ($this->filesystemIo->mv($src, $dst)) {
                    $this->logger->info('FTP: File moved: from ' . $src . ' to ' . $dst);
                } else {
                    $this->logger->warning('FTP: File NOT moved: from ' . $src . ' to ' . $dst);
                    $result = false;
                }
            };
        }
        $this->logger->info('FTP: closed result = ' . $this->close());

        return $result;
    }
}