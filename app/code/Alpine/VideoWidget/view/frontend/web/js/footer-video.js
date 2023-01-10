/**
 * Alpine_VideoWidget
 *
 * @category    Alpine
 * @package     Alpine_VideoWidget
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Lev Zamansky <lev.zamanskiy@alpineinc.com>
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function($, modal) {
    "use strict";

    $.widget('alpine.footerVideo', {
        options: {
            type: 'popup',
            responsive: true,
            buttons: [],
            iframeSelect : "youtubeVideo",
            elementSelect : ".youtubeVideo",
            height: 240,
            width: 480
        },

        _create: function () {
            var self = this;
            this.modal = $('<iframe/>', {
                'id': self.options.iframeSelect
            });

            this.options.modalClass = 'footer-video';

            if($(self.options.elementSelect).length) {
                modal(this.options, this.modal);

                //Set class to display play image over if <img> wrapped <a>
                $(self.options.elementSelect).each(function() {
                    if ($(this).has("img").length) {
                        $(this).addClass("imgAfter");
                    }
                });

                this._bindEvents();
            }
        },

        _bindEvents: function(){
            var self = this;

            //Open modal on click on all elements with class elementSelect from options
            this.element.on('click', self.options.elementSelect, function(e) {
                e.preventDefault();
                self.modal.modal('openModal');

                self.modal.attr("src", $(this).data("video"));

                if ($(this).data("width")) {
                    self.modal.data("width", $(this).data("width"));
                } else {
                    self.modal.data("width", self.options.width);
                }

                if ($(this).data("height")) {
                    self.modal.data("height", $(this).data("height"));
                } else {
                    self.modal.data("height", self.options.height);
                }

                self.resizeModal();

                $(window).on("resize", function () {
                    self.resizeModal();
                });
            });

            //Clear src after close modal for stop video
            this.modal.on('modalclosed', function() {
                self.modal.attr('src', "");
            });
        },


        /** Resize iframe and modal wrapper on window resize
         *
         */
        resizeModal: function(){
            var $modal = this.modal;

            if ($(window).width() > $modal.data("width")) {
                $(".footer-video .modal-inner-wrap").width($modal.data("width"));
                $modal.attr("width", $modal.data("width"));
            } else {
                $(".footer-video .modal-inner-wrap").width($(window).width());
                $modal.attr("width", $(window).width());
            }

            $modal.attr("height", $modal.data("height"));
        }
    });

    return $.alpine.footerVideo;
});