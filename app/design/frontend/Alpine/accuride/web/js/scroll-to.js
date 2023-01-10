/**
 * @category    Alpine
 * @theme       accuride
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Kirilll Kosonogov <kirill.kosonogov@alpineinc.com>
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    "use strict";

    $.widget('alpine.scrollToElement', {
        options: {
            fixedHeaderSelector: "[id='store.menu']",
            timer: 500
        },
        _create: function () {
            var hash = window.location.hash;
            var element;

            if (hash) {
                element = $(hash);

                if (element.length) {
                    $("html, body").animate({
                        scrollTop: element.offset().top - $(this.options.fixedHeaderSelector).outerHeight()
                    }, this.options.timer);
                    this.scrollCarouselToElement(element);
                }
            }
        },

        scrollCarouselToElement: function (element) {

            if (element.closest('.owl-carousel') && element.closest('.owl-carousel').data('owl.carousel')) {
                element
                    .closest('.owl-carousel')
                    .trigger('to.owl.carousel', element.closest('.owl-item').index(), this.options.timer);
            }
        }
    });

    return $.alpine.scrollToElement;
});