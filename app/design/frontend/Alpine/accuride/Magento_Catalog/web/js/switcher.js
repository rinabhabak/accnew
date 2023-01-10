// <!--
// * Alpine_Catalog
// *
// * @category    Alpine
// * @package     Alpine_Accuride
// * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
// * @author      Denis Furman <denis.furman@alpineinc.com>
// -->
define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('Accuride.Switcher', {
        options: {
            bindElement: $('.switch input'),
            elementWarapper: $('.cross.switcher'),
            imgGrid: $('.main .product-item-photo.cross_change')
        },

        _create: function () {
            var self = this;
            this.options.bindElement.on("click", function () {
                var active = '';
                if ($(this).prop('checked')) {
                    active = 'cross'
                } else {
                    active = 'photo'
                }
                self.options.elementWarapper.find('.type').removeClass('active');
                self.options.elementWarapper.find('.type.'+active).addClass('active');
                self.options.imgGrid.find('span.pr_image').removeClass('active');
                self.options.imgGrid.find('span.pr_image.'+active).addClass('active');
            });
        }
    });

    return $.Accuride.Switcher;
});
