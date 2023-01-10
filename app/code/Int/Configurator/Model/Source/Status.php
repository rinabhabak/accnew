<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\Configurator\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Item status functionality model
 */
class Status extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**#@+
     * Item Status values
     */
    const STATUS_PENDING = 1;
    const STATUS_INPROCESS = 2;
    const STATUS_INREVIEW = 3;
    const STATUS_COMPLETE = 4;
    const STATUS_PURCHASE = 5;

    /**#@-*/

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::STATUS_PENDING => __('Pending'), 
            self::STATUS_INPROCESS => __('Processing'), 
            self::STATUS_INREVIEW => __('In Review'), 
            self::STATUS_COMPLETE => __('Complete'),
            self::STATUS_PURCHASE => __('Purchased')
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}