/**
 * Alpine_Storelocator
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */

define([
    "jquery",
    'jquery/jquery.cookie'
], function ($) {
    'use strict';

    var distributor = function(config) {
        $('a#product-distributor').on("click", function(e) {
            $.cookie('product', config.product);
        });
    };
    
    return distributor;
});
