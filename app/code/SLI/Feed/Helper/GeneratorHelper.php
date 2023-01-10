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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Feed generation helper
 */
class GeneratorHelper extends AbstractHelper
{
    /**
     * Price attributes that are added
     *
     * @var array
     */
    protected $priceAttributes = [
        'price',
        'tax_class_id',
        'final_price',
        'minimal_price',
        'min_price',
        'tier_price',
    ];

    /**
     * These attributes do not need to be added to the collection as they have specific logic to add them
     *
     * @var array
     */
    protected $specialAttributes = [
        'product_id', // Or entity_id
        'category_id', // addCategoryIds()
        'request_path', // addUrlRewrite()
    ];

    /**
     * These attributes should always be added to the collection
     *
     * @var array
     */
    protected $defaultRequiredAttributes = [
        'is_salable',
        'name',
        'special_from_date',
        'special_price',
        'special_to_date',
        'url_key',
        'visibility',
        'image'
    ];

    /**
     * Setup model
     *
     * @var ResourceConnection
     */
    protected $connection;

    protected $filesystem;
    protected $moduleResource;
    protected $xmlPathMap;
    protected $feedFileTemplate;

    /**
     * @var SerializerInterface
     */
    protected $serializeInterface;

    /**
     * @param Context $context
     * @param ResourceConnection $connection
     * @param Filesystem $filesystem
     * @param ResourceInterface $moduleResource
     * @param array $xmlPathMap
     * @param SerializerInterface $serializeInterface
     * @param string $feedFileTemplate
     */
    public function __construct(
        Context $context,
        ResourceConnection $connection,
        Filesystem $filesystem,
        ResourceInterface $moduleResource,
        array $xmlPathMap,
        SerializerInterface $serializeInterface,
        $feedFileTemplate
    ) {
        parent::__construct($context);
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->moduleResource = $moduleResource;
        $this->xmlPathMap = $xmlPathMap;
        $this->feedFileTemplate = $feedFileTemplate;
        $this->serializeInterface = $serializeInterface;
    }

    /**
     * Get current version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->moduleResource->getDbVersion('SLI_Feed');
    }

    /**
     * @param int $storeId
     * @param string $name
     * @return mixed
     */
    protected function getStoreConfig($storeId, $name)
    {
        if (!array_key_exists($name, $this->xmlPathMap)) {
            throw new \InvalidArgumentException(sprintf('Unknown xml path name: %s', $name));
        }

        return $this->scopeConfig->getValue(
            $this->xmlPathMap[$name],
            ScopeInterface::SCOPE_STORE,
            $storeId);
    }

    /**
     * Get a db connection.
     *
     * @return AdapterInterface
     */
    protected function getConnection()
    {
        return $this->connection->getConnection();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function logSystemSettings(LoggerInterface $logger)
    {
        $logger->debug('DB Settings:');
        $query = "SHOW VARIABLES where
                    Variable_name = 'max_allowed_packet'
                    OR Variable_name = 'wait_timeout'
                    OR Variable_name = 'connect_timeout'
                    OR Variable_name = 'innodb_buffer_pool_size'";
        foreach ($this->getConnection()->fetchAll($query) as $result) {
            $logger->debug(sprintf('  %s: %s', $result['Variable_name'], $result['Value']));
        }

        $logger->debug('PHP Settings:');
        foreach (['memory_limit', 'max_execution_time'] as $key) {
            $logger->debug(sprintf('  %s: %s', $key, ini_get($key)));
        }
    }

    /**
     * Check if feed generation allowed
     *
     * @param int $storeId
     * @return bool
     */
    public function isAllowed($storeId)
    {
        return 1 == (int)$this->getStoreConfig($storeId, 'xmlPathEnabled');
    }

    /**
     * Check if include out of stock items
     *
     * @param int $storeId
     * @return bool
     */
    public function isIncludeOutOfStock($storeId)
    {
        return 1 == (int)$this->getStoreConfig($storeId, 'xmlPathIncludeOutOfStock');
    }


    /**
     * Check if include out of stock items
     *
     * @param int $storeId
     * @return bool
     */
    public function isPriceFeedEnabled($storeId)
    {
        return 1 == (int)$this->getStoreConfig($storeId, 'xmlPathAdvancedPricing');
    }

    /**
     * Get the log level
     *
     * @param int $storeId
     * @return string
     */
    public function getLogLevel($storeId)
    {
        return $this->getStoreConfig($storeId, 'xmlPathLogLevel');
    }

    /**
     * Get the base url
     *
     * @param int $storeId
     * @return string
     */
    public function getBaseUrl($storeId)
    {
        return $this->getStoreConfig($storeId, 'xmlPathUnsecureBaseUrl');
    }

    /**
     * Retrieve the list of attributes to be used for the specified store
     *
     * @param int $storeId
     * @return array
     */
    protected function feedAttributes($storeId)
    {
        $attributeString = $this->getStoreConfig($storeId, 'xmlPathAttributesSelect');
        $attributeArray = $this->serializeInterface->unserialize($attributeString);

        if (!$attributeArray || !is_array($attributeArray)) {
            return [];
        }

        // Need to convert attributes into non-associative array to get processed.
        $attributeArrayTemp = [];
        foreach ($attributeArray as $attribute) {
            array_push($attributeArrayTemp, reset($attribute));
        }
        $attributeArray = $attributeArrayTemp;

        return $attributeArray;
    }

    /**
     * Retrieve the list of custom attributes to use
     *
     * @param int $storeId
     * @param LoggerInterface $logger
     * @return array
     */
    public function getAttributes($storeId, LoggerInterface $logger)
    {
        $attributes = $this->feedAttributes($storeId);
        $attributes = array_filter($attributes);
        $attributes = array_unique($attributes);

        // Warning regarding required/mandatory attributes that have been added to attribute list
        $disregardedAttributes = array_intersect($attributes,
                    array_merge($this->priceAttributes, $this->specialAttributes, $this->defaultRequiredAttributes));
        if ($disregardedAttributes) {
            $logger->debug(sprintf('[%s] Following custom attributes are disregarded as they are mandatory: %s',
                $storeId, implode(', ', $disregardedAttributes)
            ));
        }

        // Filter out the attributes that are already being added
        $attributes = array_diff($attributes, array_merge($this->priceAttributes, $this->specialAttributes));

        // Add in the attributes we always need
        $attributes = array_unique(array_merge($attributes, $this->defaultRequiredAttributes));

        return $attributes;
    }

    /**
     * Returns feed file template.
     *
     * @return string
     * @throws \Exception
     */
    public function getFeedFileTemplate()
    {
        $template = $this->filesystem
                ->getDirectoryRead(DirectoryList::VAR_DIR)
                ->getAbsolutePath() . $this->feedFileTemplate;
        $path = dirname($template);
        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            throw new \Exception('Feed file path could not be created.');
        }

        return $template;
    }

    /**
     * Check if include cached images
     *
     * @param int $storeId
     * @return bool
     */
    public function isCachedImageEnabled($storeId)
    {
        return 1 == (int)$this->getStoreConfig($storeId, 'xmlPathCachedImage');
    }

    /**
     * Get the cached image width
     *
     * @param int $storeId
     * @return string
     */
    protected function getCachedImageWidth($storeId)
    {
        return $this->getStoreConfig($storeId, 'xmlPathCachedImageWidth');
    }

    /**
     * Get the cached image height
     *
     * @param int $storeId
     * @return string
     */
    protected function getCachedImageHeight($storeId)
    {
        return $this->getStoreConfig($storeId, 'xmlPathCachedImageHeight');
    }

    /**
     * Retrieve the list of image size to use
     *
     * @param int $storeId
     * @return array
     */
    public function getCachedImageDimensions($storeId)
    {
        if($this->isCachedImageEnabled($storeId)) {
            $cachedImageDimensions = [
                "width" => $this->getCachedImageWidth($storeId),
                "height" => $this->getCachedImageHeight($storeId)
            ];
            return $cachedImageDimensions;
        }
        return [];
    }
}
