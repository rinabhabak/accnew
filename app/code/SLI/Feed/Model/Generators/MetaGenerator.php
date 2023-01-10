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

namespace SLI\Feed\Model\Generators;

use DateTime;
use Magento\Framework\AppInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Psr\Log\LoggerInterface;
use SLI\Feed\Helper\GeneratorHelper;
use SLI\Feed\Helper\XmlWriter;

/**
 * Class MetaGenerator
 *
 * @package SLI\Feed\Model\Generators
 */
class MetaGenerator implements GeneratorInterface
{
    /**
     * StoreManagerInterface
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Helper
     *
     * @var GeneratorHelper
     */
    protected $generatorHelper;

    /**
     * StoreManagerInterface
     *
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @param StoreManagerInterface $storeManager
     * @param GeneratorHelper $generatorHelper
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        GeneratorHelper $generatorHelper,
        ProductMetadataInterface $productMetadata)
    {
        $this->storeManager = $storeManager;
        $this->generatorHelper = $generatorHelper;
        $this->productMetadata = $productMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function generateForStoreId($storeId, XmlWriter $xmlWriter, LoggerInterface $logger)
    {
        $logger->debug(sprintf('[%s] Starting meta XML generation', $storeId));
        $logger->debug(sprintf('[%s] Writing meta', $storeId));

        $xmlWriter->startElement('meta');
        $xmlWriter->writeAttribute('storeId', $storeId);
        $this->writeMetaData($storeId, $xmlWriter, $logger);
        $xmlWriter->endElement();

        $logger->debug(sprintf('[%s] Finished writing meta', $storeId));

        return true;
    }

    /**
     * @param $storeId
     * @param XmlWriter $xmlWriter
     * @param LoggerInterface $logger
     */
    public function writeMetaData($storeId, XmlWriter $xmlWriter, LoggerInterface $logger)
    {
        $xmlWriter->writeElement('lscVersion', $this->generatorHelper->getVersion());
        $xmlWriter->writeElement('magentoVersion', $this->productMetadata->getVersion());
        $xmlWriter->writeElement('magentoEdition', $this->productMetadata->getEdition());
        $xmlWriter->writeElement('magentoName', $this->productMetadata->getName());
        $xmlWriter->writeElement('context', 'cli' == php_sapi_name() ? 'CLI' : 'UI');
        $xmlWriter->writeElement('baseUrl', $this->generatorHelper->getBaseUrl($storeId));
        $xmlWriter->writeElement('phpVersion', phpversion());
        $xmlWriter->writeElement('logStatus', $this->generatorHelper->getLogLevel($storeId));

        $created = new DateTime();
        $xmlWriter->writeElement('created', $created->format(DateTime::ISO8601));

        $includeOutOfStock = $this->generatorHelper->isIncludeOutOfStock($storeId);
        $xmlWriter->writeElement('includeOutOfStock', $includeOutOfStock ? 'true' : 'false');

        $extraAttributes = $this->generatorHelper->getAttributes($storeId, $logger);
        $xmlWriter->writeElement('extraAttributes', implode(', ', $extraAttributes));
    }
}
