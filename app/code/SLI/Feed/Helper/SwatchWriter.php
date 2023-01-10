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

use Magento\Swatches\Model\Swatch;

class SwatchWriter
{
    /**
     * Prints all swatches into a xml writer, if they are associated with the attribute key.
     * @param XMLWriter $xmlWriter
     * @param array $swatches
     * @param string $attributeKey the key of the attribute
     */
    public static function writeSwatches(XMLWriter $xmlWriter, array $swatches, $attributeKey)
    {
        if (isset($swatches[$attributeKey])) {
            SwatchWriter::writeSwatch($xmlWriter, $swatches[$attributeKey]);
        }
    }

    /**
     * Outputs a swatch into the writer.
     * @param XmlWriter $xmlWriter
     * @param array $swatch array of data for a swatch with Value and Type.
     * @return void
     */
    public static function writeSwatch(XMLWriter $xmlWriter, $swatch)
    {
        $xmlWriter->startElement('swatch_value');
        $xmlWriter->text($swatch['value']);
        $xmlWriter->endElement();

        $xmlWriter->startElement('swatch_type');
        $xmlWriter->text(SwatchWriter::getSwatchTypeText($swatch['type']));
        $xmlWriter->endElement();
    }

    /**
     * Convert the type int into a string.
     * @param int $type int of the Swatch type.
     * @return string containing text representation of swatch type.
     */
    public static function getSwatchTypeText($type)
    {
        switch ($type) {
            case Swatch::SWATCH_TYPE_TEXTUAL:
                return "TEXTUAL";
            case Swatch::SWATCH_TYPE_VISUAL_COLOR:
                return "COLOR";
            case Swatch::SWATCH_TYPE_VISUAL_IMAGE:
                return "IMAGE";
            case Swatch::SWATCH_TYPE_EMPTY:
                return "EMPTY";
            default:
                return $type;
        }
    }
}