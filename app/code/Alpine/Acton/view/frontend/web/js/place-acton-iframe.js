/**
 * Alpine_Acton
 *
 * @category    Alpine
 * @package     Alpine_Acton
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc (www.alpineinc.com)
 * @author      Aleksandr Mikhailov (aleksandr.mikhailov@alpineinc.com)
 */

define([
    'jquery'
], function ($) {
    "use strict";

    return function (config) {
        var
            email = config.email,
            actionUrl = config.actionUrl,
            isSubscribedForNewsletter = $.cookie('acton_subscribe_for_newsletter');

        if (isSubscribedForNewsletter == null) {
            isSubscribedForNewsletter = false;
        }

        var queryValues = {
            'First Name': config.first_name,
            'Last Name': config.last_name,
            'Email Address': config.email,
            'Company': config.company,
            'Phone Number': config.phone_number,
            'Optin': isSubscribedForNewsletter
        };

        $.cookie('acton_subscribe_for_newsletter', false, {path: '/', expires: -1});

        var queryPart = [];
        for (var varName in queryValues) {
            if (queryValues.hasOwnProperty(varName)) {
                queryPart.push(encodeURIComponent(varName) + '=' + queryValues[varName]);
            }
        }

        queryPart = queryPart.join('&');
        var iframeUrl = actionUrl + '?' + queryPart;

        $('<iframe>', {
            src: iframeUrl,
            frameborder: 0,
            style: 'position: absolute; bottom: 0px; right: 0px; border: none; height: 1px; width: 1px; background: none',
            scrolling: 'no'
        }).appendTo('body');
    }
});