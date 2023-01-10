/**
 * Alpine_Catalog
 *
 * @category    Alpine
 * @package     Alpine_Accuride
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 * @author      Derevyanko Evgeniy (evgeniy.derevyanko@alpineinc.com)
 */

define([
    "jquery",
    "jquery/ui",
    "Magento_ConfigurableProduct/js/configurable"
], function ($) {
    'use strict';

    $.widget('alpine.configurable', $.mage.configurable, {
        options: {
            priceHolderSelector: '.product-info-main .price-box'
        },
        
        /**
         * Populates an option's selectable choices.
         * @private
         * @param {*} element - Element associated with a configurable option.
         */
        _fillSelect: function (element) {
            var attributeId = element.id.replace(/[a-z]*/, ''),
                options = this._getAttributeOptions(attributeId),
                prevConfig,
                index = 1,
                allowedProducts,
                i,
                j;

            this._clearSelect(element);
            element.options[0] = new Option('', '');
            element.options[0].innerHTML = element.config.label;
            prevConfig = false;

            if (element.prevSetting) {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }
            if (options) {
                for (i = 0; i < options.length; i++) {
                    allowedProducts = [];
                    /* eslint-disable max-depth */
                    if (prevConfig) {
                        for (j = 0; j < options[i].products.length; j++) {
                            // prevConfig.config can be undefined
                            if (prevConfig.config &&
                                prevConfig.config.allowedProducts &&
                                prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                allowedProducts.push(options[i].products[j]);
                            }
                        }
                    } else {
                        allowedProducts = options[i].products.slice(0);
                    }

                    if (allowedProducts.length > 0) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                        var disabled = true;

                        if (prevConfig) {
                            $.each(prevConfig.config.count, function (key, value) {
                                if (key in options[i].count && options[i].count[key] > 0) {
                                    disabled = false;
                                    return false;
                                }
                            });
                        } else {
                            $.each(options[i].count, function (key, value) {
                                if (value > 0) {
                                    disabled = false;
                                    return false;
                                }
                            });
                        }

                        element.options[index].disabled = disabled;

                        if (typeof options[i].price !== 'undefined') {
                            element.options[index].setAttribute('price', options[i].prices);
                        }

                        element.options[index].config = options[i];
                        index++;
                    }
                }
            }
        },
    });

    return $.alpine.configurable;
});
