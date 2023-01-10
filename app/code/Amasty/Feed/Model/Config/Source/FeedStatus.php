<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class FeedStatus
 */
class FeedStatus implements ArrayInterface
{
    /**#@+
     * Feed status
     */
    const FAILED = 3;
    const PROCESSING = 2;
    const READY = 1;
    const NOT_GENERATED = 0;
    /**#@-*/

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach (static::toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public static function toArray()
    {
        return [
            self::FAILED => __('Failed'),
            self::PROCESSING => __('Processing'),
            self::READY => __('Ready'),
            self::NOT_GENERATED => __('Not yet Generated'),
        ];
    }
}
