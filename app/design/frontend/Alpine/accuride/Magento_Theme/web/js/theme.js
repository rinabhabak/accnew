// <!--
// * Alpine_Theme
// *
// * @category    Alpine
// * @package     Alpine_Accuride
// * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
// * @author      Denis Furman <denis.furman@alpineinc.com>
// * @author      Kirill Kosonogov <kirill.kosonogov@alpineinc.com>
// -->

define([
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/ie-class-fixer',
    'domReady!'
], function ($, keyboardHandler) {
    'use strict';

    if ($('body').hasClass('checkout-cart-index')) {
        if ($('#co-shipping-method-form .fieldset.rates').length > 0 &&
            $('#co-shipping-method-form .fieldset.rates :checked').length === 0
        ) {
            $('#block-shipping').on('collapsiblecreate', function () {
                $('#block-shipping').collapsible('forceActivate');
            });
        }
    }

    if ($('body').hasClass('catalog-category-view')) {
        $('body').on('click', '.toggle_button', function (e) {
            e.preventDefault();
            $(this).toggleClass('active');
            $('.category-view').slideToggle()
        })
    }

    $('.cart-summary').mage('sticky', {
        container: '#maincontent'
    });

    $('.panel.header > .header.links').clone().appendTo('#store\\.links');

    // Add wrapper for img in navigation
    $('.nav-item .nav-anchor img').each(function () {
        $(this).wrap('<div class="wrapper_img"></div>');
    });

    //Position navigation
    function changeSize() {
        var width = 1400;
        var windowWidth = $(window).width();
        var padding = 0;
        if (windowWidth > width) {
            padding = (windowWidth - width) / 2;
        }
        $('.nav-item.logo').css({'padding-left': padding});
        $('.nav-item.minicart').css({'padding-right': padding});
    }
   
    function equalHeight(group) {
        //group.css('height','auto'); 
        var tallest = 0;
        group.each(function() {
            var thisHeight = $(this).height();
            if(thisHeight > tallest) {
                tallest = thisHeight;
            }
        });
        group.height(tallest);
    }

    $(window).resize(function () {
        changeSize();
        if($(window).width() > '600'){
            equalHeight($(".pagebuilder-column .product-item-link"));
        } else{
            $('.pagebuilder-column').find('.product-item-link').removeAttr('style');
        }      
    });  
    
    $(document).ready(function($) {    
        if($(window).width() >= '600'){
            equalHeight($(".pagebuilder-column .product-item-link"));
        }    
    });

    keyboardHandler.apply();
});