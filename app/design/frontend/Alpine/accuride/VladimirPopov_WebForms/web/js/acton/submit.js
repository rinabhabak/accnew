/**
 * VladimirPopov_WebForms
 *
 * @category    Alpine
 * @theme       Alpine_Accuride
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 * @author      Alex Didenko <alex.didenko@alpineinc.com>
 */

define(["jquery"], function($) {
    "use strict";

    var acton = {
        submitForm: function(data, googleKey, pageName, formId) {
            var formName = $(
                "#webform_" + formId + ' input[name="formname"]'
            ).val();
            var leadSource = $(
                "#webform_" + formId + ' input[name="lead_source"]'
            ).val();

            if (leadSource != "") {
                gtag("event", "cf7_submission", {
                    event_category: "Form: " + pageName,
                    event_label: leadSource,
                    event_action: formName + " submit"
                });
            }

            var url = data.url,
                fields = data.fields,
                hiddenFields = {},
                iframe;

            document
                .querySelectorAll(
                    "#webform_" + formId + ' input[type="hidden"]'
                )
                .forEach(function(el) {
                    hiddenFields[el.name] = el.value;
                });
            $.extend(fields, hiddenFields);
            console.log(hiddenFields);
            var queryString = $.param(fields);
            console.log(queryString);
            if (url) {
                var iFrameUrl = url + "?" + queryString;
                iframe = $("<iframe>", {
                    id: "acton",
                    src: iFrameUrl,
                    frameborder: 0,
                    style:
                        "position: absolute; bottom: 0px; right: 0px; border: none; height: 1px; width: 1px; background: none",
                    scrolling: "no"
                }).appendTo("body");
            }

            return iframe;
        }
    };

    return acton;
});
