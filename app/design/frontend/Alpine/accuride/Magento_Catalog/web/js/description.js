define([
    "jquery"
], function ($) {
    "use strict";

    $.widget('alpine.readmore', {
        options: {
            wrapperclass: 'show-more-wrapper',
            showMoreText: 'Show more',
            showLessText: 'Show less',
            showMoreHeight: 45
        },

        _create: function () {
            this.element.wrap('<div class="' + this.options.wrapperclass + '"></div>');

            if (this.element.prop('scrollHeight') > this.options.showMoreHeight) {
                this.moreLink = this._addMoreLink();

                this._bindEvents();
            }
        },

        _addMoreLink: function() {
            var self = this;
            return $('<a/>',{
                'class': 'show-more',
                'text': self.options.showMoreText
            }).insertAfter(this.element);
        },

        _bindEvents: function() {
            var self = this;
            this.moreLink.on('click', function(e){
                e.preventDefault();
                self.element.closest('.' + self.options.wrapperclass).toggleClass('expanded');
                if(self.element.closest('.' + self.options.wrapperclass).hasClass('expanded')){
                    $(this).text(self.options.showLessText);
                }else{
                    $(this).text(self.options.showMoreText);
                }
            })
        }
    });

    return $.alpine.readmore;
});