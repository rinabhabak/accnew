define(
    [
        'underscore',
         'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'mage/translate',
        'jquery'
    ],
    function (_, ko,Component, setPaymentInformationAction, additionalValidators, creditCardData, cardNumberValidator, $t, $) {
        'use strict';
        var configCybersource = window.checkoutConfig.payment.magedelight_cybersource;
        
        
        return Component.extend({
            
            defaults: {
                template: 'Magedelight_Cybersource/payment/cybersource'
            },
            newCardVisible: ko.observable(false),
            newCardVisibleSave: ko.observable(false),
            savechecked: ko.observable(true),
            noSavedCardAvail: ko.observable(false),
            
           
            getCode: function () {
                return 'magedelight_cybersource';
            },
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'subscription_id': $('#'+this.getCode()+'_payment_profile_id').val(),
                        'cc_type': $('#'+this.getCode()+'_cc_type').val(),
                        'cc_number': $('#'+this.getCode()+'_cc_number').val(),
                        'expiration': $('#'+this.getCode()+'_expiration').val(),
                        'expiration_yr': $('#'+this.getCode()+'_expiration_yr').val(),
                        'cc_cid': $('#'+this.getCode()+'_cc_cid').val(),
                        'save_card': $('#'+this.getCode()+'_save_card').val(),
                    }
                };
            },
            
            getCcAvailableTypes: function() {
                return configCybersource.availableCardTypes;
            },
            getIcons: function (type) {
                return window.checkoutConfig.payment.ccform.icons.hasOwnProperty(type)
                    ? window.checkoutConfig.payment.ccform.icons[type]
                    : false
            },
            getCcMonths: function() {
               // return window.checkoutConfig.payment.ccform.months[this.getCode()];
                return configCybersource.ccMonths;
            },
            getCcYears: function() {
                return window.checkoutConfig.payment.ccform.years[this.getCode()];
            },
            hasVerification: function() {
              //  return window.checkoutConfig.payment.ccform.hasVerification[this.getCode()];
                return configCybersource.hasVerification;
            },
            hasSsCardType: function() {
                return window.checkoutConfig.payment.ccform.hasSsCardType[this.getCode()];
            },
            getCvvImageUrl: function() {
                return window.checkoutConfig.payment.ccform.cvvImageUrl[this.getCode()][0];
            },
            getCvvImageHtml: function() {
                return '<img src="' + this.getCvvImageUrl()
                    + '" alt="' + $t('Card Verification Number Visual Reference')
                    + '" title="' + $t('Card Verification Number Visual Reference')
                    + '" />';
            }, 
            getSsStartYears: function() {
                return window.checkoutConfig.payment.ccform.ssStartYears[this.getCode()];
            },
            getCcAvailableTypesValues: function() {
                return _.map(this.getCcAvailableTypes(), function(value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
            getCcMonthsValues: function() {
                return _.map(configCybersource.ccMonths, function(value, key) {
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },
            getCcYearsValues: function() {
                return _.map(configCybersource.ccYears, function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            getSsStartYearsValues: function() {
                return _.map(this.getSsStartYears(), function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            isShowLegend: function() {
                return false;
            },
            getCcTypeTitleByCode: function(code) {
                var title = '';
                _.each(this.getCcAvailableTypesValues(), function (value) {
                    if (value['value'] == code) {
                        title = value['type'];
                    }
                });
                return title;
            },
            formatDisplayCcNumber: function(number) {
                return 'xxxx-' + number.substr(-4);
            },
            getInfo: function() {
                return [
                    {'name': 'Credit Card Type', value: this.getCcTypeTitleByCode(this.creditCardType())},
                    {'name': 'Credit Card Number', value: this.formatDisplayCcNumber(this.creditCardNumber())}
                ];
            },
            getStoredCard: function(){
                
                return _.map(configCybersource.storedCards, function(value, key) {
                    return {
                        'value': key,
                        'optText': value
                    }
                });
            },
            isNewEnabled: function(){
                var result = true;
                if(_.size(configCybersource.storedCards) > 1){
                    result =  false;
                }
                return result;
            },
            isStoreCardDropdownEnabled: function()
            {
                var result = false;
                if(_.size(configCybersource.storedCards) > 1){
                    result = true;
                }
                else if(_.size(configCybersource.storedCards) == 1)
                {
                    this.newCardVisible(true);
                    this.noSavedCardAvail(true);
                }
                return result;
            },
            displayNewCard: function(){
                var elementValue = $('#'+this.getCode()+'_payment_profile_id').val();
                if(elementValue == 'new'){
                    // $('#'+this.getCode()+'card-holder').css('display','block');
                     this.newCardVisible(true);
                     this.newCardVisibleSave(true);
                }else{
                    // $('#'+this.getCode()+'card-holder').css('display','none');
                     this.newCardVisible(false);
                     this.newCardVisibleSave(false);
                }
            },
            isSaveCardOptional: function(){
                return (configCybersource.canSaveCard == 0) ? true: false;
            },
            prepareCsPayment: function(){
                if ($('#cybersource-transparent-form').valid()) {
                    this.placeOrder();
                  }
            },
           
        });
    }
);