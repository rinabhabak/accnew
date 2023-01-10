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

/**
 * Class XmlWriter
 *
 * @package SLI\Feed\Helper
 */
class XmlWriter extends \XMLWriter
{
    /**
     * @param $filename
     * @param string $startElement
     */
    public function __construct($filename, $startElement = 'catalog')
    {
        $this->openUri($filename);
        $this->startDocument('1.0', 'UTF-8');
        $this->setIndent(true);
        $this->setIndentString(' ');
        // start catalog
        $this->startElement($startElement);
    }

    /**
     * Write a key value pair as XML.
     *
     * @param string $name
     * @param mixed $value - array, bool or string.
     */
    public function writeNode($name, $value)
    {
        if (null === $value) {
            // no point writing an empty node
            return;
        }

        $this->startElement($name);
        $this->text($value);
        $this->endElement();
    }


    /**
     * @param array $attributes
     */
    public function writeAttributes(array $attributes)
    {
        foreach ($attributes as $attributeKey => $attributePair ) {
            $attributeId = $attributePair[0];
            $attributeValues = $attributePair[1];
            $attributeSwatches = $attributePair[2];

            $this->startElement('attribute');

            $this->startElement('key');
            $this->text($attributeKey);
            $this->endElement();

            $this->startElement('id');
            $this->text($attributeId);
            $this->endElement();

            foreach ($attributeValues as $attributeValueKey => $attributeValue) {
                $this->startElement('attributeValue');

                $this->startElement('key');
                $this->text($attributeValueKey);
                $this->endElement();

                $this->startElement('value');
                $this->text($attributeValue);
                $this->endElement();

                SwatchWriter::writeSwatches($this, $attributeSwatches, $attributeValueKey);

                $this->endElement();
            }

            $this->endElement();
        }
    }

    /**
     *
     */
    public function closeFeed()
    {
        // main tag
        $this->endElement();

        $this->endDocument();
        $this->flush();
    }

}
