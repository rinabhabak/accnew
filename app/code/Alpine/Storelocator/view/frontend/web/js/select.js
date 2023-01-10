/**
 * Alpine_Storelocator
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 * @author      Kirill Kosonogov <kirill.kosonogov@alpineinc.com>
 */

define([
    'jquery',
    "underscore",
    "mage/translate",
    "selectTwo"
], function($,_, $t) {
    'use strict';

    var autocompleteGlobal;
    var filterData = {
    };

    $.widget('amlocator.select', {
        options: {
            filters: {},
            filtersUsedForSelect: [
                "State",
                "Region",
                "Province"
            ],
            select2Config: {
                placeholder: $t('Enter address, zip or select from dropdown'),
                width: 'style',
                matcher: function(params, data){
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    if (typeof data.children === 'undefined') {
                        return null;
                    }

                    var filteredChildren = [];
                    $.each(data.children, function (idx, child) {
                        if (child.text.toUpperCase().indexOf(params.term.toUpperCase()) == 0) {
                            filteredChildren.push(child);
                        }
                    });

                    if (filteredChildren.length) {
                        var modifiedData = $.extend({}, data, true);
                        modifiedData.children = filteredChildren;

                        if(autocompleteGlobal){
                            google.maps.event.clearInstanceListeners(autocompleteGlobal);
                            google.maps.event.clearListeners(document.querySelector('.select2-search__field'));
                            $('.pac-container').remove();
                            autocompleteGlobal = null;
                        }

                        return modifiedData;
                    }


                    return null;
                }
            },
            mapCanvas: $('#amlocator-map-canvas')
        },

        _create: function () {
            this.selectData = this._filterData();
            this.options.select2Config.data = this._transformData(this.selectData);

            filterData['attribute_id'] = _.map(this.options.filters, function(value){
                return value['attribute_id'];
            });

            this._createDefaultSelects();
            this.selectElement = this._createSelect($t('Enter address, zip or select from dropdown'));

            this._initSelect2();
            this._bindEvents();
            
            _.each(this.options.filters, function(item) {
                if (item.hasOwnProperty('selected') && item.selected) {
                    $('select.industry').change();
                    $.cookie('product', null);
                    return;
                }
            });
        },

        _createDefaultSelects: function() {
            var selectsData = _.filter(this.options.filters, function(data){
                return !~this.options.filtersUsedForSelect.indexOf(data.label);
            }, this);

            _.each(selectsData, function(item){
                 var select = this._createSelect(item.label);
                select.attr('name', 'option[' + item['attribute_id'] + ']');
                select.attr('class', item['label'].toLowerCase());

                _.each(item.options, function(optionText, optionId){
                    $("<option>",{
                        'value': optionId,
                        'text': optionText
                    }).appendTo(select);
                });
                
                if (item.hasOwnProperty('selected') && item.selected) {
                    select.val(item.selected);
                }
            }, this)
        },

        _createSelect: function(text){
            text = text ? text : 'Please Select';
            return $('<select/>').append('<option value="">' + text + '</option>').appendTo(this.element);
        },

        _filterData: function(){
            return _.filter(this.options.filters, function(data){
                return !!~this.options.filtersUsedForSelect.indexOf(data.label);
            }, this);
        },

        _transformData: function (data){
            return _.map(data, function(item){
                return {
                    "text": item["label"],
                    "children": this._createOptionsData(item.options)
                };
            }, this);
        },

        _createOptionsData: function(data){
            return _.map(data, function(item, key){
                return {
                    "id": key,
                    "text": item
                };
            })
        },

        _initSelect2: function (){
            var self = this;
            _.each(this.selectData, function(item, indx){
                var temp = [];
                _.each(item.options, function(optionText, optionId){
                    temp.push([optionText,optionId]);
                });
                temp.sort();
                var tempObj = [];
                _.each(temp, function(item){
                    tempObj.push({id: item[1], text: item[0]});
                });
                self.options.select2Config.data[indx].children = tempObj;
            });
            this.selectElement.select2(self.options.select2Config)
        },

        _bindEvents: function() {
            var self = this;

            this.selectElement.data('select2').on("results:message", $.proxy(function(event){
                this._googleAutocomplete.call(this, event);
            }, this));

            this.selectElement.on("select2:opening", $.proxy(function(){
                this.selectElement.find("[data-option]").remove().trigger("change");

            }, this));

            this.element.find('select').not(this.selectElement).on('change', $.proxy(function(event){
                filterData[event.target.name] = event.target.value;

                var coordinates = this._getCoordinates();

                if(coordinates.lat && coordinates.lng){
                    this.options.mapCanvas.data('alpineAmLocator').sortByFilter();
                }else{
                    this.options.mapCanvas.data('alpineAmLocator').filterByAttribute($.param(filterData));
                }
            }, this));
            
            this.selectElement.on("select2:select", $.proxy(function(event){
                var selecterFilter = _.find(this.selectData, function(item){
                    return !!item.options[event.params.data.id]
                });

                this._setCoordinates({
                    'lat': "",
                    'lng': ""
                });

                filterData = {
                    attribute_id: Object.keys(this.options.filters)
                };

                if(selecterFilter && selecterFilter.attribute_id){
                    filterData["option[" + selecterFilter.attribute_id + "]"] = event.params.data.id;
                }

                //this.options.mapCanvas.data('alpineAmLocator').filterByAttribute($.param(filterData));
                $('select.industry').change();

            },this))
        },

        _setCoordinates: function(coordinates){
            document.getElementById("am_lat").value = coordinates.lat;
            document.getElementById("am_lng").value = coordinates.lng;
        },

        _getCoordinates: function(){
            return {
                'lat': document.getElementById("am_lat").value,
                'lng': document.getElementById("am_lng").value
            }
        },

        _googleAutocomplete: function(){
            var self = this;
            var address = document.querySelector('.select2-search__field');

            if(!autocompleteGlobal){
                autocompleteGlobal = new google.maps.places.Autocomplete(address);
                google.maps.event.addListener(autocompleteGlobal, 'place_changed', function () {
                    var place = autocompleteGlobal.getPlace();

                    self._setCoordinates({
                        'lat': place.geometry.location.lat(),
                        'lng': place.geometry.location.lng(),
                        'address': place.formatted_address
                    });

                    var option = $('<option/>',{
                        'text': place.formatted_address,
                        'selected': true,
                        'data-option': 'search-option'
                    });

                    self.selectElement.prepend(option).trigger('change');

                    self.options.mapCanvas.data('alpineAmLocator').sortByFilter();
                });
            }else{
                google.maps.event.trigger(document.querySelector('.select2-search__field') ,'place_changed');
            }
        }
    });

    return $.amlocator.select;
});