/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @category    Alpine
 * @theme       accuride
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Kirilll Kosonogov <kirill.kosonogov@alpineinc.com>
 */

var config = {
    shim: {
        owlCarousel: {
            deps: ['jquery']
        },
        slick: {
            deps: ['jquery']
        },
        mCustomScrollbar: {
            deps: ['jquery']
        }
    },
    paths: {
        owlCarousel: 'js/lib/owl.carousel',
        menuAim: 'js/lib/jquery.menu-aim',
        desktopNav: 'js/desktop-nav',
        slick: 'js/lib/slick.min',
        mCustomScrollbar: 'js/lib/jquery.mCustomScrollbar.concat.min'
    }
};