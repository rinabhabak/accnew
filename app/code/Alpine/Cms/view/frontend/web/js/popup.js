/**
 * Alpine_Cms
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Aleksandr Mikhailov <aleksandr.mikhailov@alpineinc.com>
 */

define([
    'jquery'
], function($) {

    $('div.product_info_static ul li:eq(0)').click(function(e) {
        e.preventDefault();
        $('.cms-page-popup.warranty').modal('openModal');
    });

    $('div.product_info_static ul li:eq(1)').click(function(e) {
        e.preventDefault();
        $('.cms-page-popup.support').modal('openModal');
    });

    $('div.product_info_static ul li:eq(2)').click(function(e) {
        e.preventDefault();
        $('.cms-page-popup.shipping').modal('openModal');
    });
});