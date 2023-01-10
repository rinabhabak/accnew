/**
 * @category    Alpine
 * @theme       accuride
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Kirilll Kosonogov <kirill.kosonogov@alpineinc.com>
 */

define([
    'jquery',
    'matchMedia',
    'menuAim'
],function($, mediaCheck){
    "use strict";

    function activateSubmenu(row) {
        var $row      = $(row),
            $submenu  = $row.children('.submenu');

        $submenu.show();
        $row.find("a").addClass("maintainHover");
    }

    function deactivateSubmenu(row) {
        var $row      = $(row),
            $submenu  = $row.children('.submenu');

        $submenu.hide();
        $row.find("a").removeClass("maintainHover");
    }

    function exitMenu(menu) {
        $(menu).find('.maintainHover').removeClass("maintainHover");
        $(menu).find('li.level0 > .submenu').hide();
        return true;
    }

    return function(opts, elem) {
        var $elem = $(elem);

        mediaCheck({
            media: '(min-width: 768px)',
            entry: function () {
                $('.ves-megamenu').menuAim(
                    $.extend({},{
                        activate        : activateSubmenu,
                        deactivate      : deactivateSubmenu,
                        exitMenu: exitMenu,
                        submenuDirection: 'below',
                        relativeContainer: $('.header-sticky')
                    },opts)
                );
            }.bind(this),
            exit: function () {
                $('.ves-megamenu').menuAim(
                    $.extend({},{
                        destroy: true
                    },opts)
                );
            }.bind(this)
        });
    }
});