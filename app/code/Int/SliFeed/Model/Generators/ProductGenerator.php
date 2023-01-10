<?php

namespace Int\SliFeed\Model\Generators;

use Magento\Catalog\Model\Product;
use SLI\Feed\Helper\XmlWriter;

class ProductGenerator extends \SLI\Feed\Model\Generators\ProductGenerator
{
    protected function writeProduct(XmlWriter $xmlWriter, Product $product, array $attributes = [], array $extraAttributes = [])
    {
        // function to write a single (structured) value
        $writeValue = function ($value, $level, $writeValue) use ($xmlWriter) {
            if (is_array($value)) {
                foreach ($value as $fieldName => $fieldValue) {
                    $displayFieldName = 'value_' . $level;
                    if (is_string($fieldName)) {
                        $displayFieldName = $fieldName;
                    }
                    $xmlWriter->startElement($displayFieldName);
                    $writeValue($fieldValue, $level + 1, $writeValue);
                    $xmlWriter->endElement();
                }
            } elseif (is_bool($value)) {
                $xmlWriter->text($value ? 'true' : 'false');
            } else {
                if(!is_object($value)){
                    $xmlWriter->text($value);
                }
            }
        };
        // function to add a single element with some support for types
        $writeElement = function ($name, $value) use ($xmlWriter, $writeValue) {
            $xmlWriter->startElement($name);
            $writeValue($value, 1, $writeValue);
            $xmlWriter->endElement();
        };

        $xmlWriter->startElement('product');
        $xmlWriter->writeAttribute('id', $product->getId());
        $xmlWriter->writeAttribute('sku', $product->getSku());

        // regular attributes
        foreach ($attributes as $attribute) {
            $writeElement($attribute, $product->getData($attribute));
        }

        // Array of XML Node Name => Product Object function call which obtains the ids for each relationship type.
        $linkedAttributesFunctionMap = [
            'related_products' => 'getRelatedProductIds',
            'upsell_products' => 'getUpsellProductIds',
            'crosssell_products' => 'getCrossSellProductIds'
        ];

        foreach ($linkedAttributesFunctionMap as $label => $method) {
            if (in_array($label, $extraAttributes)) {
                $writeElement($label, $product->{$method}());
            }
        }

        $type = $product->getTypeInstance();
        $childrenIds = $type->getChildrenIds($product->getId());
        $writeElement('child_ids', $childrenIds);

        $this->writeStockInformation($writeElement, $product, $extraAttributes);

        // custom properties
        $properties = [
            'CategoryIds' => 'categories',
            'IsVirtual' => 'is_virtual'
        ];
        foreach ($properties as $property => $element) {
            $method = 'get' . $property;
            $value = $product->$method();
            $writeElement($element, $value);
        }

        // product
        $xmlWriter->endElement();
    }

    /**
     * Write XML for stock summary
     *
     * @param $writeElement
     * @param Product $product
     * @param array $extraAttributes
     * @return void
     */
    private function writeStockInformation($writeElement, Product $product, array $extraAttributes = []) {
        if (array_search('stock_summary', $extraAttributes) !== false) {
            $stockContents = $this->stockLoader->getStockStatus($product);
            $writeElement('stock_summary', $stockContents);
        }
    }
}