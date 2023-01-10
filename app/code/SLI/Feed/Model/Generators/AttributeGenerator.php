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

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Swatches\Helper\Data as SwatchHelper;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use SLI\Feed\Helper\GeneratorHelper;
use SLI\Feed\Helper\XmlWriter;

/**
 * Class AttributeGenerator
 *
 * @package SLI\Feed\Model\Generators
 */
class AttributeGenerator extends AbstractModel implements GeneratorInterface
{
    /**
     * Attribute collection
     *
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * Attribute Values array
     *
     * @var array
     */
    protected $attributeValues;

    /**
     * Attributes with index (not label) value.
     *
     * @var string[]
     */
    protected $indexValueAttributes = [];

    /**
     * Helper
     *
     * @var GeneratorHelper
     */
    protected $generatorHelper;

    /**
     * Swatch Helper
     *
     * @var SwatchHelper
     */
    protected $swatchHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param CollectionFactory $attributeCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param GeneratorHelper $generatorHelper
     * @param SwatchHelper $swatchHelper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        CollectionFactory $attributeCollectionFactory,
        StoreManagerInterface $storeManager,
        GeneratorHelper $generatorHelper,
        SwatchHelper $swatchHelper
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager
        );

        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->generatorHelper = $generatorHelper;
        $this->swatchHelper = $swatchHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function generateForStoreId($storeId, XmlWriter $xmlWriter, LoggerInterface $logger)
    {
        $logger->debug(sprintf('[%s] Starting attribute XML generation', $storeId));
        $this->initAttributes($storeId, $logger);

        $logger->debug(sprintf('[%s] Writing attributes', $storeId));

        $xmlWriter->startElement('attributes');

        if ($this->attributeValues) {
            $xmlWriter->writeAttributes($this->attributeValues);
        }

        // attributes
        $xmlWriter->endElement();

        $logger->debug(sprintf('[%s] Finished writing attributes', $storeId));

        return true;
    }

    /**
     * Create the attribute values for a store
     *
     * @param int $storeId
     * @param LoggerInterface $logger
     */
    protected function initAttributes($storeId, LoggerInterface $logger)
    {

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeCollection */
        $attributeCollection = $this->attributeCollectionFactory->create();

        $this->writeCollection($attributeCollection, $storeId, $logger);
    }

    /**
     * Write Collection
     *
     * @param Collection $attributeCollection
     * @param int $storeId
     * @param LoggerInterface $logger
     * @return void
     */
    public function writeCollection(Collection $attributeCollection, $storeId, LoggerInterface $logger)
    {
        $attributeCollection
            ->addStoreLabel($storeId);

        $customAttributes = $this->generatorHelper->getAttributes($storeId, $logger);

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $page = 0;
        $pageSize = 1000;
        $attributeCollection->setPageSize($pageSize);
        $attributeCollection->setCurPage($page);
        while ($items = $attributeCollection->getItems()) {
            foreach ($items as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                $attributeId = $attribute->getAttributeId();
                // Only add the options that we require
                if (in_array($attributeCode, $customAttributes)) {
                    $attributeOptions = $this->getAttributeOptions($attribute, $storeId, $logger);

                    $swatches = $this->getSwatches($attribute, $attributeOptions);

                    // Only add the attributes that have options
                    if ($attributeOptions) {
                        $this->attributeValues[$attributeCode] = [$attributeId, $attributeOptions, $swatches];
                    }
                }
            }
            if (count($items) < $pageSize) {
                break;
            }

            $attributeCollection->setCurPage(++$page);
            $attributeCollection->clear();
        }
    }

    /**
     * Returns attributes all values in label-value or value-value pairs form. Labels are lower-cased.
     *
     * @param AbstractAttribute $attribute
     * @param int $storeId
     * @param LoggerInterface $logger
     * @return array
     */
    protected function getAttributeOptions(AbstractAttribute $attribute, $storeId, LoggerInterface $logger)
    {
        $options = [];

        if ($attribute->usesSource()) {
            // should attribute has index (option value) instead of a label?
            $index = in_array($attribute->getAttributeCode(), $this->indexValueAttributes) ? 'value' : 'label';

            // Retrieve the current store values
            $attribute->setStoreId($storeId);

            try {
                foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                    foreach (is_array($option['value']) ? $option['value'] : [$option] as $innerOption) {
                        if (strlen($innerOption['value'])) {
                            // skip ' -- Please Select -- ' option
                            $options[$innerOption['value']] = $innerOption[$index];
                        }
                    }
                }
            } catch (\Exception $e) {
                // ignore exceptions connected with source models
                $logger->warning(sprintf('[%s] Failed to get attribute options: %s',
                    $storeId, $e->getMessage()),
                    ['exception' => $e]
                );
            }
        }

        return $options;
    }

    /**
     * Returns an array of swatches, or empty array
     * @param $attribute
     * @param $attributeOptions
     * @return array of swatches, or empty array
     */
    protected function getSwatches($attribute, $attributeOptions)
    {
        $swatches = [];
        if ($this->swatchHelper->isSwatchAttribute($attribute)) {
            $swatches = $this->swatchHelper->getSwatchesByOptionsId(array_keys($attributeOptions));
        }
        return $swatches;
    }
}
