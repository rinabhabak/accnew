// <!--
// * Alpine_Catalog
// *
// * @category    Alpine
// * @package     Alpine_Accuride
// * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
// * @author      Kirill Kosonogov <kirill.kosonogov@alpineinc.com>
// -->
define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('Accuride.itemHover', {
        options: {
            hoverClass: 'hovered',
            itemSelector: '.product-item'
        },

        _create: function () {
            this._bindEvents();
        },

        _bindEvents: function () {
            var self = this;
            this.element.on('focus', 'select',function () {
                $(this).closest(self.options.itemSelector).addClass(self.options.hoverClass)
            })
                .on('blur', 'select', function () {
                    $(this).closest(self.options.itemSelector).removeClass(self.options.hoverClass)
                });
        }
    });

    return $.Accuride.itemHover;
});
