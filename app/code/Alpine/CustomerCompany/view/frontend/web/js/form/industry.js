/**
 * Alpine_CustomerCompany
 *
 * @category    Alpine
 * @package     Alpine_CustomerCompany
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    var industry = function(config) {
        var industrySelect = '#' + config.selectCode,
            otherIndustry = 'div.field-' + config.textfieldCode;
        
        $(industrySelect).on('change', function() {
            // If 'Other' is selected then input text is showed
            if ($(this).children(':selected').text() == 'Other') {
                $(otherIndustry).removeClass('hidden');
            } else {
                $(otherIndustry).addClass('hidden');
            }
        });
    };
    
    return industry;
});
