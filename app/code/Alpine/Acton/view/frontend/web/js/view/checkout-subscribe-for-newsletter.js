/**
 * Alpine_Acton
 *
 * @category    Alpine
 * @package     Alpine_Acton
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc (www.alpineinc.com)
 * @author      Aleksandr Mikhailov (aleksandr.mikhailov@alpineinc.com)
 */

define([
    'ko',
    'jquery',
    'uiComponent'
], function (ko, $, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Alpine_Acton/checkout/checkout-subscribe-for-newsletter'
        },
        initObservable: function () {
            this._super()
                .observe({
                    isSubscribeForNewsletter: ko.observable(false)
                });

            return this;
        },
        showSubscribeForNewsletterBlock: ko.computed(function () {
            return true;
        }),
        subscribeForNewsletter: function() {

            if (this.isSubscribeForNewsletter()) {
                $.cookie('acton_subscribe_for_newsletter', true, {path: '/'});
            } else {
                $.cookie('acton_subscribe_for_newsletter', false, {path: '/', expires: -1});
            }

            return true;
        },
        getCode: function (parent) {
            return _.isFunction(parent.getCode) ? parent.getCode() : 'shared';
        }
    });
});
