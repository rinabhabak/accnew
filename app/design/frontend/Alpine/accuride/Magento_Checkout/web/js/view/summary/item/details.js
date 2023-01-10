/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details'
        },

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },

        /**
         * @param {Object} itemId
         * @return {String}
         */
        getSku: function (itemId) {
            var itemsData = window.checkoutConfig.quoteItemData;
            var prodSku = null;
            itemsData.forEach(function (item) {
                if (item.item_id == itemId) {
                    prodSku = item.sku;
                }
            });
            if (prodSku != null) {
                return prodSku;
            } else {
                return '';
            }
        }
    });
});
