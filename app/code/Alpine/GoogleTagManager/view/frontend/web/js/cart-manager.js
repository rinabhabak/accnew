/**
 * Alpine_GoogleTagManager
 *
 * @category    Alpine
 * @package     Alpine_GoogleTagManager
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc (www.alpineinc.com)
 * @author      Evgeniy Derevyanko <evgeniy.derevyanko@alpineinc.com>
 */

define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_GoogleTagManager/js/google-analytics-universal',
    'Magento_GoogleTagManager/js/google-analytics-universal-cart',
    'underscore',
    'jquery/ui'
], function ($, customerData, GoogleAnalyticsUniversal, GoogleAnalyticsUniversalCart, _) {

    return function (target) {
        $.widget('mage.gtmCart', target, {
            _create: function () {
                this._super();
                var self = this;
                $('#product-addtocart-button').click(function () {
                    self._setCartDataListener();
                });
            },

            getProductBySku: function (sku) {
                var newCart = customerData.get('cart')().items,
                    oldCart = this.cartItemsCache;

                var result = [];

                newCart.each(function(newItem){
                    var tmpProduct = newItem;
                    oldCart.each(function(oldItem){
                        if (newItem.item_id === oldItem.item_id) {
                            if (newItem.qty > oldItem.qty) {
                                tmpProduct.qty -= oldItem.qty;
                            } else {
                                tmpProduct = 0;
                            }
                        }
                    });

                    if (tmpProduct) {
                        result.push(tmpProduct);
                    }
                });
                return result;
            },

            _executeEvents: function () {
                this.options.temporaryEventStorage.forEach(function (item, index) {
                    this.options.actions[item.type](this.getProductBySku(item.sku));
                    this.options.temporaryEventStorage.splice(index, 1);
                }.bind(this));
            },

            _initActions: function () {
                var events = this.options.events;

                this.options.actions[events.AJAX_ADD_TO_CART] = function (product) {
                    this.googleAnalyticsUniversal.addToCart(
                        product['product_sku'],
                        product['product_name'],
                        product['product_price_value'],
                        product.qty,
                        product
                    );
                }.bind(this);

                this.options.actions[events.AJAX_REMOVE_FROM_CART] = function (product) {
                    this.googleAnalyticsUniversal.removeFromCart(
                        product['product_sku'],
                        product['product_name'],
                        product['product_price_value'],
                        product.qty
                    );
                }.bind(this);
            },

            _setCartDataListener: function () {
                customerData.get('cart').subscribe(function (data) {
                    if (this.options.temporaryEventStorage.length) {
                        this._executeEvents();
                    }

                    this.cartItemsCache = data.items.slice();
                }.bind(this));

                if (JSON.parse(localStorage.getItem('mage-cache-storage')).cart) {
                    this.cartItemsCache = JSON.parse(localStorage.getItem('mage-cache-storage')).cart.items;
                } else if (customerData.get('cart')().items) {
                    this.cartItemsCache = customerData.get('cart')().items.slice();
                } else {
                    this.cartItemsCache = [];
                }
            }
        });

        return $.mage.gtmCart;
    };
});