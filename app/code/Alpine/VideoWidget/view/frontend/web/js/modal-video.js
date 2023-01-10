define([
        'jquery',
        'Magento_Ui/js/modal/modal'
    ], function($, modal) {
        "use strict";

        $.widget('alpine.modalVideo', {
            options: {
                type: 'popup',
                responsive: true,
                modalClass: 'modal-video',
                clickableOverlay: true,
                buttons: []
            },

            _create: function () {
                    this._bind();
            },

            _bind: function () {
                var $btn = this.element,
                    $modal = $btn.closest('.video__wrap').find('.video__body iframe'),
                    popup = modal(this.options, $modal);

                $modal.on('modalclosed', function() {
                    $modal.attr('src', $modal.attr('src'));
                });

                $btn.on('click', function (e) {
                    e.preventDefault();
                    $modal.modal('openModal');
                });
            }
        });

        return $.alpine.modalVideo;
});