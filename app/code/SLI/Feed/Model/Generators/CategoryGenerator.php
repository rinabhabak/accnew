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

use Exception;
use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\State;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use SLI\Feed\Helper\XmlWriter;

/**
 * Class CategoryGenerator
 *
 * @package SLI\Feed\Model\Generators
 */
class CategoryGenerator extends AbstractModel implements GeneratorInterface
{
    /**
     * Category collection factory
     *
     * @var CollectionFactory
     */
    protected $entityCollectionFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ResourceModel\Category $resource
     * @param Collection $resourceCollection
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        ResourceModel\Category $resource,
        Collection $resourceCollection
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $resource,
            $resourceCollection,
            []
        );

        $this->entityCollectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function generateForStoreId($storeId, XmlWriter $xmlWriter, LoggerInterface $logger)
    {

        $logger->debug(sprintf('[%s] Starting category XML generation', $storeId));

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $entityCollection */
        $entityCollection = $this->entityCollectionFactory->create();
        $this->writeCollection($entityCollection, $storeId, $xmlWriter, $logger);

        return true;
    }

    /**
     * Write Collection
     *
     * @param Collection $entityCollection
     * @param int $storeId
     * @param XmlWriter $xmlWriter
     * @param LoggerInterface $logger
     * @return void
     * @throws Exception
     */
    public function writeCollection(Collection $entityCollection, $storeId, XmlWriter $xmlWriter, LoggerInterface $logger)
    {
        $entityCollection
            ->setStoreId($storeId)
            ->setProductStoreId($storeId)
            ->addAttributeToSelect(['*']);

        $page = 0;
        $processed = 0;
        $pageSize = 1000;

        $xmlWriter->startElement('categories');

        $entityCollection->setPage(++$page, $pageSize);
        while ($items = $entityCollection->getItems()) {
            foreach ($items as $item) {
                ++$processed;
                $this->writeCategory($xmlWriter, $item);
            }

            if (count($items) < $pageSize) {
                break;
            }

            $entityCollection->setPage(++$page, $pageSize);
            $entityCollection->clear();
        }

        // categories
        $xmlWriter->endElement();

        $logger->debug(sprintf('[%s] Category generator: processed items: %s, pages: %s', $storeId, $processed, $page));
    }

    /**
     * Write a single category
     *
     * @param XmlWriter $xmlWriter
     * @param Category $category
     */
    protected function writeCategory(XmlWriter $xmlWriter, Category $category)
    {
        $xmlWriter->startElement('category');

        $xmlWriter->writeAttribute('id', $category->getId());
        $xmlWriter->writeAttribute('name', $category->getName());
        $xmlWriter->writeAttribute('active', $category->getIsActive() ? 1 : 0);
        $xmlWriter->writeAttribute('parent', $category->getParentId());

        $xmlWriter->endElement();
    }
}
