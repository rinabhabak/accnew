/**
 * Alpine_Cms
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Valery Shishkin <valery.shishkin@alpineinc.com>
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

define([
    'jquery',
    'mage/translate',
    'jquery/ui'
], function($, $t) {

    $.widget('alpine.contentPagination', {
        options: {
            itemsPerPage: 16,
            navClass: '.pagination'
        },
        
        itemsPerPage: 16,
        pagesCount: 1,
        
        _create: function () {
            var self = this,
                items = this.element.find('.group > .item'),
                nav = this.element.find(this.options.navClass + ' > ul');
            this.itemsPerPage = parseInt(this.options.itemsPerPage);
            if (items.length) {
                nav.append(self._addPage(1));
                nav.find('.page').addClass('current');
                nav.find('.pages-item-previous').hide();
            }
            items.each(function (index) {
                var page = Math.round(index / self.itemsPerPage);
                if (index && index % self.itemsPerPage == 0) {
                    nav.append(self._addPage(page + 1));
                    self.pagesCount = page + 1;
                }
            });
            this.element.addClass('ui-pagination-paginated');
            this.element.find('.action').on('click', function (e) {
                e.preventDefault();
                var current = self.element.find('.page.current').data('ui-pagination-page');
                if ($(this).hasClass('next')) {
                    self.selectPage(current + 1);
                } else {
                    self.selectPage(current - 1);
                }
            });
            self.selectPage(1);
        },
        
        _addPage: function (page) {
            var self = this,
                link = $('<a href="#" class="page" data-ui-pagination-page="' + page +'"></a>');
            link.append('<span class="label">' + $t('Page') + '</span><span>' + page + '</span>');
            link.on('click', function(e) {
                e.preventDefault();
                if (!$(this).hasClass('current')) {
                    self.selectPage($(this).data('ui-pagination-page'));
                }
            });
            return $('<li class="item"></li>').append(link);
        },
        
        selectPage: function (page) {
            var self = this,
                nav = this.element.find(this.options.navClass + ' > ul');
            this.element.find('.group > .item').each(function (index) {
                if (self.itemsPerPage * (page - 1) <= index && index < self.itemsPerPage * page) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            nav.find('.pages-item-previous').hide();
            nav.find('.pages-item-next').hide();
            if (page > 1) {
                nav.find('.pages-item-previous').show();
            }
            if (page < this.pagesCount) {
                nav.find('.pages-item-next').show();
            }
            
            nav.find('.page.current').removeClass('current');
            nav.find('.page[data-ui-pagination-page=' + page +']').addClass('current');
        }
    });
    return $.alpine.contentPagination;
});