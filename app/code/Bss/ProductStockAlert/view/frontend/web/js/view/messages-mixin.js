/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery'
], function ($) {
    'use strict';

    var messagesMixin = {
        /**
         * Bind stock input css
         */
        bindStockCss: function () {
            $('div#product_stock_alert_container').trigger('contentUpdated');
        }
    };

    /**
     * @inheritDoc
     */
    return function (target) {
        return target.extend(messagesMixin);
    };
});
