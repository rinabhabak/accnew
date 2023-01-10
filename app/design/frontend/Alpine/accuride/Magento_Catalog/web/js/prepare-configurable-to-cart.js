// <!--
// * Alpine_Catalog
// *
// * @category    Alpine
// * @package     Alpine_Accuride
// * @copyright   Copyright (c) 2019 Alpine Consulting, Inc
// * @author      Denis Furman <denis.furman@alpineinc.com>
// * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
// -->
define([
    'jquery',
    'underscore',
    'priceBox',
    "mage/mage",
    "mage/validation"
], function ($, _) {
    'use strict';

    window.accuride = {};
    window.accuride.redirectUrl = {};

    $.widget('Accuride.PrepareConfigurableToCart', {
        options: {
            container: '.product-item-info',
            superAttribute: '[name^=super_attribute]',
            priceBox: '[data-role=priceBox]',
            form: '[data-role=tocart-form]',
            selectedOption: '[name=selected_configurable_option]',
            superAttr: '[name^=super_attribute]',
            qty: '[name=qty]',
            image: '.product-item-photo',
            productDetails: '.product-item-details',
            simpleProduct: '.product-item'
        },

        _create: function () {
            window.accuride.config = this.options;

            var self = this;
            var container = $(this.options.container);

            var list = $('.product-item.conf');
            if (list.length !== 0) {
                list.hover(this.alignOptions);
            }

            container.find(this.options.superAttribute).on('change', function() {
                var element = $(this),
                    attributeId = element.attr('name').match(/\d+/)[0];

                self.updateOptionsVisibillity(
                    this.closest(window.accuride.config.simpleProduct),
                    false,
                    this
                );
                self.updateData(this);
                self.outofstockpopup(this,this.options.simpleProduct);

            });

            var $variations = $('select[id^="attribute_"]').parents(this.options.simpleProduct);
            $variations.each(function(index, variation) {
                self.updateOptionsVisibillity(variation, true);
            });
        },

        updateOptionsVisibillity: function (
            elem,
            isFirst,
            select = false
        ) {
            var self = this,
                productId = elem.id,
                config = window.accuride.config,
                countOptions = config.spConfig[productId].count;

            self.productElem = $(elem);

            if (isFirst) {
                var firstAttributeId = Object.keys(countOptions)[0];
                self.selectElem = self.productElem.find(
                    'select[name=super_attribute\\[' + firstAttributeId + '\\]]'
                );

                self.selectElem.prop('disabled', false);
                $.each(countOptions[firstAttributeId], function (optionId, products) {

                    if((products.backorder == 0 && products.isInStock == false) || (products.backorder == 1 && products.isInStock == false)) {
                          //self.selectElem.find('[value=' + optionId + ']').prop('disabled', true);
                        self.selectElem.find('[value=' + optionId + ']').text(self.selectElem.find('[value=' + optionId + ']').text()+'(Out of Stock)');  
                        
                        
                                    }
                    else if(products.customStockStatus) {
                        self.selectElem.find('[value=' + optionId + ']').text(self.selectElem.find('[value=' + optionId + ']').text()+' ('+products.customStockStatus+')');
                    }

                });
            } else if (select) {
                self.selectElem = $(select);
                self.selectedAttributeId = self.selectElem.attr('name').match(/\d+/)[0];
                self.countOptions = countOptions;
                self.isCurrentSelect = false;
                self.isNext = false;
                self.commonProducts = [];

                self.productElem.find('select[name^=super_attribute]').each(function() {
                    var currentAttributeId = $(this).attr('name').match(/\d+/)[0];

                    if (self.isNext) {
                        //$(this).prop('disabled', true);
                    }

                    if (self.isCurrentSelect) {
                        self.currentSelect = self.productElem.find(
                            'select[name=super_attribute\\[' + currentAttributeId + '\\]]'
                        );

                        $(this).prop('disabled', false);
                        $(this).val('');
                        //self.currentSelect.find('option').not('[value=""]').prop('disabled', true);
                        $.each(self.countOptions[currentAttributeId], function(currentOptionId, products) {
                            $.each(self.commonProducts, function (index, productId) {
                                if (productId in products && products[productId] > 0) {
                                    self.currentSelect
                                        .find('[value=' + currentOptionId + ']')
                                        .prop('disabled', false);

                                    return false;
                                }
                            });
                        });

                        self.isCurrentSelect = false;
                        self.isNext = true;
                    } else {
                        var secondArray = _.keys(self.countOptions[currentAttributeId][$(this).val()]);
                        if (self.commonProducts.length) {
                            self.commonProducts = _.intersection(
                                self.commonProducts,
                                secondArray
                            );
                        } else {
                            self.commonProducts = secondArray;
                        }
                    }

                    if (self.selectedAttributeId == currentAttributeId) {
                        self.isCurrentSelect = true;
                    }
                });
            }
        },

        isProductInStock: function (products) {

            var result = false,
                condition;
            condition = _.find(products, function (value) {
                return value > 0;
            });

            if (condition) {
                result = true;
            }

            return result;
        },

        alignOptions: function (el) {
            var itemInfo = $(this);
            var confOptions = itemInfo.find('.conf_options');

            if (confOptions.height() == 0) {

                var itemName = itemInfo.find('.product-item-name');
                var itemNameMargin = parseFloat(itemName.css('margin-top')) + parseFloat(itemName.css('margin-bottom'));
                var finalPrice = itemInfo.find('.price-box.price-final_price');
                var review = itemInfo.find('.wrapper_review');
                var description = itemInfo.find('.description');
                var itemInner = itemInfo.find('.product-item-inner');

                confOptions.height(itemInfo.height() - itemName.height() - finalPrice.height() - review.height() - description.height() - itemInner.height() - itemNameMargin);
            }
        },

        outofstockpopup: function (elem) {
            var element = $(elem),
                attrName = element.attr('name'),
                config = window.accuride.config,
                spConfig = config.spConfig,
                countOptions,
                container,
                priceBox,
                configurableId,
                associativeProductId,
                associativeProductStock;

            container = element.closest(config.container);
            priceBox = container.find(config.priceBox);
            configurableId = priceBox.attr('data-product-id');
            countOptions = spConfig[configurableId].count;
            var firstAttributeId = Object.keys(countOptions)[0];


            $.map(Object.values(countOptions)[0], function(value, index) {
    
                if(index == elem.value) {
                    associativeProductStock = value;
                    associativeProductId = Object.keys(value)[0];
                }       

            });

            if((associativeProductStock.backorder == 0 && associativeProductStock.isInStock == false) || (associativeProductStock.backorder == 1 && associativeProductStock.isInStock == false))
                {
                    $.ajax({
                        context: '.amxnotif-container-1',
                        url: BASE_URL+'outofstock/form/index',
                        type: "POST",
                        showLoader: true
                    }).done(function (data) {
                        $('.intnotif-container').html(data.output);
                        $('.configurable-product-popup form').attr('id','form-validate-stock-'+associativeProductId);
                        $('.configurable-product-popup form .notification-container').attr('id','notification-container-'+associativeProductId);
                        $('.configurable-product-popup form .amxnotif-guest-email').attr('id','amxnotif-guest-email-'+associativeProductId);
                        $(".configurable-product-popup form input[name='product_id'").val(associativeProductId);
                        $(".configurable-product-popup form input[name='parent_id'").val(configurableId);
                        //$(".configurable-product-popup form .amxnotif-guest-email").val('');

                        $('.configurable-product-popup').show();
                        $('#form-validate-stock-'+associativeProductId).mage('validation');
                    
                    return true;
                    });
    
                }
        },
        updateData: function (elem) {

            var element = $(elem),
                attrName = element.attr('name'),
                config = window.accuride.config,
                spConfig = config.spConfig,
                addToCartUrl = config.spConfig['add-to-cart-url'],
                redirectUrl = window.accuride.redirectUrl,
                mapper = {},
                container,
                priceBox,
                form,
                configurableId,
                qty,
                attrId;

            

            if (addToCartUrl.charAt(addToCartUrl.length - 1) === "/") {
                addToCartUrl = addToCartUrl.substr(0, addToCartUrl.length - 1);
            }

            addToCartUrl = addToCartUrl.split('/');
            addToCartUrl.splice(-1, 1);
            attrId = attrName.match(/\d+/)[0];
            container = element.closest(config.container);
            priceBox = container.find(config.priceBox);
            configurableId = priceBox.attr('data-product-id');
            qty = container.find('.input-text.qty').val();
            form = container.find(config.form);
            mapper[attrId] = element.val();
            form.find(config.qty).val(qty);
            form.find('.' + element.attr('id')).val(element.val());

            var self = this,
                productId = elem.id,
                countOptions = config.spConfig[configurableId].count;
            var firstAttributeId = Object.keys(countOptions)[0];

            if (element.siblings(config.superAttr).length) {
                var laps = element.siblings(config.superAttr).length;

                for (var i = 0; i < laps; i++) {
                    var currentSibling = element.siblings(config.superAttr).eq(i);
                    attrName = currentSibling.attr('name');
                    attrId = attrName.match(/\d+/)[0];
                    if(currentSibling.val() !='')
                    mapper[attrId] = currentSibling.val();
                    else
                    mapper[attrId] = jQuery('#attribute_'+attrId+'_'+configurableId+" option:eq(1)").val();
                }
            }
            
            for (var prop in mapper) {
                if (mapper[prop] == "") {
                    form.find(config.selectedOption).val("");
                    form.attr('action', redirectUrl[configurableId]);
                    return;
                }
            }
            var arr = {};
            if (spConfig[configurableId]) {
                var simpleId = _.findKey(spConfig[configurableId], mapper);
                if (spConfig[configurableId][simpleId]) {
                    form.find(config.selectedOption).val(simpleId);
                    if (!redirectUrl[configurableId]) {
                        redirectUrl[configurableId] = form.attr('action');
                    }
                    form.attr('action', addToCartUrl.join('/') + '/' + configurableId + '/');

                    this.calculatePrice(configurableId, simpleId);

                }
                
            }
            
        },

        calculatePrice: function(configurableId, simpleId) {
            var config = window.accuride.config,
                newPrices = config.spConfig[configurableId].optionPrices[simpleId],
                priceConfig = config.priceConfigs[configurableId],
                priceBoxSelector = '#' + configurableId + ' .price-box';

            $(priceBoxSelector).priceBox({'priceConfig': priceConfig});

            var displayPrices = $(priceBoxSelector).priceBox('option').prices,
                priceBoxPrices = {};

            _.each(displayPrices, function (price, code) {
                if (newPrices[code]) {
                    displayPrices[code].amount = newPrices[code].amount - displayPrices[code].amount;
                }
            });

            priceBoxPrices[configurableId] = displayPrices;
            $(priceBoxSelector).trigger('updatePrice', priceBoxPrices);
        }
    });

    return $.Accuride.PrepareConfigurableToCart;
});