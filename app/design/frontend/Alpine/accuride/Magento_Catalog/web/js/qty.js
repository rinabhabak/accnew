// <!--
// * Alpine_Catalog
// *
// * @category    Alpine
// * @package     Alpine_Accuride
// * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
// * @author      Denis Furman <denis.furman@alpineinc.com>
// * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
// -->
define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('Accuride.QtyUpdate', {
        options: {
            updateElementSelector: '.update',
            qtyElementSelector: 'input.qty',
            qtyFormField: 'input[name="qty"]',
            maxQty: Infinity
        },

        _create: function () {
            this._bindEvents();
        },

        _bindEvents: function(){
            var self = this,
                input = self.element.find(self.options.qtyElementSelector),
                qtyFormField = self.element.find(self.options.qtyFormField),
                maxQty = self.options.maxQty ? self.options.maxQty : Infinity;

            this.element.on('click', this.options.updateElementSelector, function() {
                var currentVal = input.val();

                if ($(this).hasClass('plus') && currentVal < maxQty) {
                    input.val(++currentVal);
                    qtyFormField.val(currentVal);
                } else if ($(this).hasClass('minus') && currentVal >= 1) {
                    input.val(--currentVal);
                    qtyFormField.val(currentVal);
                }
            });

            this.element.on('keyup mouseup', this.options.qtyElementSelector, function() {
                var currentVal = input.val();

                if (currentVal > maxQty) {
                    input.val(maxQty);
                    qtyFormField.val(maxQty);
                } else if (currentVal < 1) {
                    qtyFormField.val(1);
                } else {
                    qtyFormField.val(currentVal);
                }
            });
        }
    });

    return $.Accuride.QtyUpdate;
});
