/**
 * Alpine_Storelocator
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 * @author      Aleksandr Mikhailov (aleksandr.mikhailov@alpineinc.com)
 */

define([
    "jquery",
    "jquery/ui",
    "Amasty_Storelocator/js/main"
], function ($) {
    'use strict';

    $.widget('alpine.amLocator', $.mage.amLocator, {
        startPoint: 'Current+Location',

        _create: function () {
            this.url = this.options.ajaxCallUrl;
            this.filterAttributeUrl = this.options.filterAttributeUrl;
            this.useGeo = this.options.useGeo;
            this.imageLocations = this.options.imageLocations;
            this.Amastyload();
            var self = this;
            $('#locateNearBy').click(function(){
                self.navigateMe()
            });
            $('#sortByFilter').click(function(){
                self.sortByFilter()
            });

            $('#filterAttribute').click(function(){
                self.filterByAttribute()
            });
            $("[name='leftLocation']").click(function(){
                var id =  $(this).attr('data-amid');
                self.gotoPoint(id, this);
                self.openMapsDirection(id);
            });

            if ( (navigator.geolocation) && (this.useGeo == 1) ) {
                navigator.geolocation.getCurrentPosition( function(position) {
                    document.getElementById("am_lat").value = position.coords.latitude;
                    document.getElementById("am_lng").value = position.coords.longitude;
                }, this.navigateFail );
            }

            $( ".today_schedule" ).click(function(event) {
                $(this).next( ".all_schedule" ).toggle( "slow", function() {
                    // Animation complete.
                });
                $(this).find( ".locator_arrow" ).toggleClass("arrow_active");
                event.stopPropagation();
            });
        },

        filterByAttribute: function(){
            var self = this,
                    form = $("#attribute-form").serialize(),
                    stateSelect = $('select.select2-hidden-accessible');

            if(arguments.length){
                form = arguments[0];
            }

            $.ajax({
                url     : this.filterAttributeUrl,
                type    : 'POST',
                data: {"attributes": form},
                showLoader: true
            }).done($.proxy(function(response) {
                response = JSON.parse(response);
                $("#amlocator_left").replaceWith(response.block);
                self.options.jsonLocations = response;
                self.Amastyload(response);

                if (stateSelect.val()) {
                    self.startPoint = stateSelect.find(':selected').text();
                }

                $("[name='leftLocation']").click(function(){
                    var id =  $(this).attr('data-amid');
                    self.gotoPoint(id, this);
                    self.openMapsDirection(id);
                });
                $( ".today_schedule" ).click(function(event) {
                    $(this).next( ".all_schedule" ).toggle( "slow", function() {
                        // Animation complete.
                    });
                    $(this).find( ".locator_arrow" ).toggleClass("arrow_active");
                    event.stopPropagation();
                });
            }));
        },

        sortByFilter: function() {

            var e = document.getElementById("amlocator-radius");
            var radius = e.options[e.selectedIndex].value;
            var lat = document.getElementById("am_lat").value;
            var lng = document.getElementById("am_lng").value;
            if (!lat || !lng) {
                alert('Please fill Current Location field');
                return false;
            }
            var self = this,
                industry = $('.select.industry').val();

            $.ajax({
                url     : this.url,
                type    : 'POST',
                data: {
                    "lat": lat,
                    "lng": lng,
                    "radius": radius,
                    "industry": industry
                },
                showLoader: true
            }).done($.proxy(function(response) {
                response = JSON.parse(response);
                $("#amlocator_left").replaceWith(response.block);
                self.options.jsonLocations = response;
                self.Amastyload(response);
                self.startPoint = lat + ',' + lng;
                $("[name='leftLocation']").click(function(){
                    var id =  $(this).attr('data-amid');
                    self.gotoPoint(id, this);
                    self.openMapsDirection(id);
                });
                $( ".today_schedule" ).click(function(event) {
                    $(this).next( ".all_schedule" ).toggle( "slow", function() {
                        // Animation complete.
                    });
                    $(this).find( ".locator_arrow" ).toggleClass("arrow_active");
                    event.stopPropagation();
                });
            }));
        },

        openMapsDirection: function (endPoint) {
            var self = this,
                    googleMapsUrl = 'https://www.google.com/maps/dir/',
                    endPointPosition = self.marker[endPoint - 1].position,
                    endPointCoordinates = endPointPosition.lat() + ',' + endPointPosition.lng();
            window.open(googleMapsUrl + self.startPoint + '/' + endPointCoordinates, '_blank');
        },

        showAttributeInfo: function (curtemplate, item, currentStoreId) {
            var attributeTemplate = baloonTemplate.attributeTemplate;
            if (item.attributes) {
                for (var j = 0; j < item.attributes.length; j++) {
                    var label = item.attributes[j].frontend_label;
                    var labels = item.attributes[j].labels;
                    if (labels[currentStoreId]) {
                        label = labels[currentStoreId];
                    }

                    var value = item.attributes[j].value;
                    if (item.attributes[j].boolean_title) {
                        value = item.attributes[j].boolean_title;
                    }
                    if (item.attributes[j].option_title) {
                        var optionTitles = item.attributes[j].option_title;
                        value = '<br>';
                        for (var k = 0; k < optionTitles.length; k++) {
                            value += '- ';
                            if (optionTitles[k][currentStoreId]) {
                                value += optionTitles[k][currentStoreId];
                            } else {
                                if (optionTitles[k][1]) {
                                    value += optionTitles[k][1];
                                } else {
                                    value += optionTitles[k][0];
                                }
                            }
                            value += '<br>';
                        }
                    }
                    attributeTemplate = attributeTemplate.replace("{{title}}",label);
                    curtemplate += attributeTemplate.replace("{{value}}",value);

                    attributeTemplate = baloonTemplate.attributeTemplate;
                }
            }
            return curtemplate;
        }
    });

    return $.alpine.amLocator;
});
