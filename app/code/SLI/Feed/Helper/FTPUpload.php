<?php
/**
 * Copyright (c) 2015 S.L.I. Systems, Inc. (www.sli-systems.com) - All Rights Reserved
 * This file is part of Learning Search Connect.
 * Learning Search Connect is distributed under a limited and restricted
 * license – please visit www.sli-systems.com/LSC for full license details.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE. TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO
 * EVENT WILL SLI BE LIABLE TO YOU OR ANY OTHER PARTY FOR ANY GENERAL, DIRECT,
 * INDIRECT, SPECIAL, INCIDENTAL OR CONSEQUENTIAL LOSS OR DAMAGES OF ANY
 * CHARACTER ARISING OUT OF THE USE OF THE CODE AND/OR THE LICENSE INCLUDING
 * BUT NOT LIMITED TO PERSONAL INJURY, LOSS OF DATA, LOSS OF PROFITS, LOSS OF
 * ASSIGNMENTS, DATA OR OUTPUT FROM THE SERVICE BEING RENDERED INACCURATE,
 * FAILURE OF CODE, SERVER DOWN TIME, DAMAGES FOR LOSS OF GOODWILL, BUSINESS
 * INTERRUPTION, COMPUTER FAILURE OR MALFUNCTION, OR ANY AND ALL OTHER DAMAGES
 * OR LOSSES OF WHATEVER NATURE, EVEN IF SLI HAS BEEN INFORMED OF THE
 * POSSIBILITY OF SUCH DAMAGES.
 */

namespace SLI\Feed\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\System;
use Magento\Store\Model\ScopeInterface;
use SLI\Feed\Logging\LoggerFactoryInterface;

/**
 * Class FTPUpload
 *
 * This class allows for Uploading a single file to the FTP configured via the admin console.
 *
 * @package SLI\Feed\Helper
 */
class FTPUpload extends AbstractHelper
{
    const XML_PATH_FTP_ENABLED = 'sli_feed_generation/ftp/enabled';
    const XML_PATH_FTP_USER = 'sli_feed_generation/ftp/user';
    const XML_PATH_FTP_PASS = 'sli_feed_generation/ftp/password';
    const XML_PATH_FTP_HOST = 'sli_feed_generation/ftp/host';
    const XML_PATH_FTP_PORT = 'sli_feed_generation/ftp/port';
    const XML_PATH_FTP_PATH = 'sli_feed_generation/ftp/upload_path';

    /* @var string */
    protected $ftpUser = null;
    protected $ftpPass = null;
    protected $ftpHost = null;
    protected $ftpPath = null;
    protected $ftpPort = '21';

    /**
     * Ftp Client
     *
     * @var System\Ftp
     */
    protected $ftpClient = null;

    /**
     * Logger collection factory
     *
     * @var LoggerFactoryInterface
     */
    protected $loggerFactory;

    /**
     * Encryptor
     * Used to decrypt the ftp password from the database.
     *
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * Constructor
     *
     * @param Context $context
     * @param LoggerFactoryInterface $loggerFactory
     * @param EncryptorInterface $encryptor
     */
    public function __construct(Context $context, LoggerFactoryInterface $loggerFactory, EncryptorInterface $encryptor)
    {
        parent::__construct($context);
        $this->loggerFactory = $loggerFactory;
        $this->encryptor = $encryptor;
        $this->ftpClient = new System\Ftp($context);
    }

    /**
     * Upload a file from disk to the ftp configured for a particular store.
     *
     * @param string $localFilePath The local file path.
     * @param int $storeId
     *
     * @return bool True on success or False on failure
     * @throws \Exception
     */
    public function writeFileToFTP($localFilePath, $storeId)
    {
        $logger = $this->loggerFactory->getStoreLogger($storeId);

        // If no ftp client initialised, abort!
        if (null === $this->ftpClient) {
            $logger->error(sprintf('[%s] No FTP Client object initialised', $storeId));

            return false;
        }

        /* Calculate connection string and attempt connection to server. */
        try {
            $logger->debug(sprintf('[%s] Connecting to FTP Server...', $storeId));
            $connectionString = $this->getFtpConnectString($storeId);
            $this->ftpClient->connect($connectionString);
            $this->ftpClient->pasv(true);
            $logger->debug(sprintf('[%s] Connected to FTP Server.', $storeId));
        } catch (\Exception $e) {
            // don't show the full exception as that expose the password!
            $logger->error(sprintf('FTP could not connect for storeId [%s]', $storeId), ['exception' => $e]);

            if ('cli' != php_sapi_name()) {
                // rethrow exception so that it can be handled by FeedManager and show it in UI
                throw new \Exception(sprintf('FTP could not connect for storeId [%s]', $storeId));
            }

            return false;
        }

        /* Calculate FTP Path and attempt to upload local file to server. */
        $uploadSuccessful = false;
        try {
            $ftpServerLocation = $this->ftpPath . '/' . basename($localFilePath);
            $logger->debug(sprintf('[%s] Attempting to upload file: %s', $storeId, $localFilePath));
            $logger->debug(sprintf('[%s] To server location: %s', $storeId, $ftpServerLocation));

            $uploadSuccessful = $this->ftpClient->upload($ftpServerLocation, $localFilePath);
        } catch (\Exception $e) {
            $logger->error(sprintf('[%s] FTP failed to write to [%s]: %s',
                $storeId, $this->ftpPath, $e->getMessage()),
                ['exception' => $e]
            );

            if ('cli' != php_sapi_name()) {
                // rethrow exception so that it can be handled by FeedManager and show it in UI
                throw new \Exception($e);
            }
        } finally {
            $this->ftpClient->close();
        }

        /* Output the overall status of the upload. */
        if ($uploadSuccessful) {
            $logger->debug(sprintf('[%s] Upload Successful', $storeId));
        } else {
            $logger->error(sprintf('[%s] Upload Failed', $storeId));
        }

        return $uploadSuccessful;
    }

    /**
     * Obtain the ftp string to be used in FTP Connection phase
     * Example: ftp://username:password@domain.com:port/path
     *
     * @param int $storeId
     *
     * @return String
     */
    protected function getFtpConnectString($storeId)
    {
        $this->setFTPConfigFromDB($storeId);

        $connectionString = 'ftp://' . $this->ftpUser . ':' . $this->ftpPass . '@' . $this->ftpHost . ':' . $this->ftpPort;

        return $connectionString;
    }

    /**
     * Obtain the settings from the database which the user provided in the UI
     *
     * @param int $storeId
     */
    protected function setFTPConfigFromDB($storeId)
    {
        $this->ftpHost = $this->scopeConfig->getValue(FTPUpload::XML_PATH_FTP_HOST,
            ScopeInterface::SCOPE_STORE,
            $storeId);
        $this->ftpPort = $this->scopeConfig->getValue(FTPUpload::XML_PATH_FTP_PORT,
            ScopeInterface::SCOPE_STORE,
            $storeId);
        $this->ftpUser = $this->scopeConfig->getValue(FTPUpload::XML_PATH_FTP_USER,
            ScopeInterface::SCOPE_STORE,
            $storeId);
        $this->ftpPath = $this->scopeConfig->getValue(FTPUpload::XML_PATH_FTP_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId);
        $this->ftpPass = $this->encryptor->decrypt($this->scopeConfig->getValue(FTPUpload::XML_PATH_FTP_PASS,
            ScopeInterface::SCOPE_STORE,
            $storeId));
    }

    /**
     * Check if ftp upload allowed
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function isAllowed($storeId)
    {
        return 1 == (int)$this->scopeConfig->getValue(
            static::XML_PATH_FTP_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId);
    }
}
