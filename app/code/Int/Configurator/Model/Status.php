<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\Configurator\Model;

class Status
{
    const STATUS_PENDING = 1;
    const STATUS_INPROCESS = 2;
    const STATUS_INREVIEW = 3;
    const STATUS_COMPLETE = 4;
    const STATUS_PURCHASED = 5;

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
            self::STATUS_PURCHASED => __('Purchased')
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public static function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}