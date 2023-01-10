<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Cybersource
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Cybersource\Model\Source;

class Additionalfield implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('--Please Select--'),
            ],
            [
                'value' => 'store_url',
                'label' => __('Store URL'),
            ],
            [
                'value' => 'store_name',
                'label' => __('Store Name'),
            ],
            [
                'value' => 'order_id',
                'label' => __('Order Id #'),
            ],
            [
                'value' => 'shipping_amount',
                'label' => __('Shipping Amount'),
            ],
            [
                'value' => 'shipping_name',
                'label' => __('Shipping Method Name'),
            ],
            [
                'value' => 'discount',
                'label' => __('Discount'),
            ],
            [
                'value' => 'coupon',
                'label' => __('Coupon Code'),
            ],
            [
                'value' => 'coupon',
                'label' => __('Coupon Code'),
            ],
        ];
    }
}
