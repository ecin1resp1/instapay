define([
    'underscore',
    'jquery',
    'ko',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Payment/js/model/credit-card-validation/credit-card-data',
    'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
    'mage/translate',
    'mage/url',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Checkout/js/action/place-order',
], function (
    _,
    $,
    ko,
    Component,
    creditCardData,
    cardNumberValidator,
    $t,
    urlBuilder,
    additionalValidators,
    redirectOnSuccessAction,
    placeOrderAction
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'ECInternet_InstaPAY/payment/instapay',
            creditCardType: '',
            creditCardExpYear: '',
            creditCardExpMonth: '',
            creditCardNumber: '',
            creditCardSsStartMonth: '',
            creditCardSsStartYear: '',
            creditCardSsIssue: '',
            creditCardVerificationNumber: '',
            selectedCardType: null
        },

        errorValidationMessage: ko.observable(false),
        instapayPaymentUrl: urlBuilder.build('instapay/payment/quicksale'),
        instapayPaymentData: {},

        initObservable: function () {
            this._super()
                .observe([
                    'creditCardType',
                    'creditCardExpYear',
                    'creditCardExpMonth',
                    'creditCardNumber',
                    'creditCardVerificationNumber',
                    'creditCardSsStartMonth',
                    'creditCardSsStartYear',
                    'creditCardSsIssue',
                    'selectedCardType'
                ]);
            return this;
        },

        initialize: function() {
            var self = this;
            this._super();

            //Set credit card number to credit card data object
            this.creditCardNumber.subscribe(function(value) {
                var result;
                self.selectedCardType(null);

                if (value == '' || value == null) {
                    return false;
                }
                result = cardNumberValidator(value);

                if (!result.isPotentiallyValid && !result.isValid) {
                    return false;
                }
                if (result.card !== null) {
                    self.selectedCardType(result.card.type);
                    creditCardData.creditCard = result.card;
                }

                if (result.isValid) {
                    creditCardData.creditCardNumber = value;
                    self.creditCardType(result.card.type);
                }
            });

            //Set expiration year to credit card data object
            this.creditCardExpYear.subscribe(function(value) {
                creditCardData.expirationYear = value;
            });

            //Set expiration month to credit card data object
            this.creditCardExpMonth.subscribe(function(value) {
                creditCardData.expirationYear = value;
            });

            //Set cvv code to credit card data object
            this.creditCardVerificationNumber.subscribe(function(value) {
                creditCardData.cvvCode = value;
            });
        },

        /**
         * Get code
         * @returns {String}
         */
        getCode: function () {
            return 'instapay';
        },

        /**
         * Get active status
         * @returns {Boolean}
         */
        isActive: function () {
            return true;
        },

        /**
         * Get data
         * @returns {Object}
         */
        getData: function () {
            return {
                'method': this.item.method,
                'additional_data': {
                    'cc_cid': this.creditCardVerificationNumber(),
                    'cc_ss_start_month': this.creditCardSsStartMonth(),
                    'cc_ss_start_year': this.creditCardSsStartYear(),
                    'cc_ss_issue': this.creditCardSsIssue(),
                    'cc_type': this.creditCardType(),
                    'cc_exp_year': this.creditCardExpYear(),
                    'cc_exp_month': this.creditCardExpMonth(),
                    'cc_number': this.creditCardNumber()
                }
            };
        },

        /**
         * Get list of available credit card types
         * @returns {Object}
         */
        getCcAvailableTypes: function() {
            return window.checkoutConfig.payment.instapay.availableTypes[this.getCode()];
        },

        /**
         * Get payment icons
         * @param {String} type
         * @returns {Boolean}
         */
        getIcons: function (type) {
            return window.checkoutConfig.payment.ccform.icons.hasOwnProperty(type) ?
                window.checkoutConfig.payment.ccform.icons[type]
                : false;
        },

        /**
         * Get list of months
         * @returns {Object}
         */
        getCcMonths: function() {
            return window.checkoutConfig.payment.instapay.months[this.getCode()];
        },

        /**
         * Get list of years
         * @returns {Object}
         */
        getCcYears: function() {
            return window.checkoutConfig.payment.instapay.years[this.getCode()];
        },

        /**
         * Check if current payment has verification
         * @returns {Boolean}
         */
        hasVerification: function() {
            return window.checkoutConfig.payment.instapay.hasVerification[this.getCode()];
        },

        /**
         * Get list of available credit card types values
         * @returns {Object}
         */
        getCcAvailableTypesValues: function() {
            return _.map(this.getCcAvailableTypes(), function(value, key) {
                return {
                    'value': key,
                    'type': value
                }
            });
        },

        /**
         * Get list of available month values
         * @returns {Object}
         */
        getCcMonthsValues: function() {
            return _.map(this.getCcMonths(), function(value, key) {
                return {
                    'value': key,
                    'month': value
                }
            });
        },

        /**
         * Get list of available year values
         * @returns {Object}
         */
        getCcYearsValues: function() {
            return _.map(this.getCcYears(), function(value, key) {
                return {
                    'value': key,
                    'year': value
                }
            });
        },

        /**
         * Get image url for CVV
         * @returns {String}
         */
        getCvvImageUrl: function () {
            return window.checkoutConfig.payment.instapay.cvvImageUrl[this.getCode()];
        },

        /**
         * Get image for CVV
         * @returns {String}
         */
        getCvvImageHtml: function () {
            return '<img src="' + this.getCvvImageUrl() +
                '" alt="' + $t('Card Verification Number Visual Reference') +
                '" title="' + $t('Card Verification Number Visual Reference') +
                '" />';
        },

        /**
         * Action to place order
         * @returns {Boolean}
         */
        placeOrder: function (data, event) {
            var self = this;

            if (event) {
                event.preventDefault();
            }
            if (!this.getPaymentInfo() || !additionalValidators.validate()) {
                return false;
            }

            $.ajax({
                url: this.instapayPaymentUrl,
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                processData: false,
                data: JSON.stringify(this.instapayPaymentData),
                showLoader: true,
                success: function (data) {
                    self.isPlaceOrderActionAllowed(false);
                    if (data.status == 1) {
                        self.getPlaceOrderDeferredObject()
                        .fail(
                            function () {
                                self.isPlaceOrderActionAllowed(true);
                            }
                        ).done(
                            function () {
                                self.afterPlaceOrder();
                                if (self.redirectAfterPlaceOrder) {
                                    redirectOnSuccessAction.execute();
                                }
                            }
                        );
                    }
                },
                error: function(result) {
                    var errorMessage = 'Something went wrong. Please try again later.';
                    self.errorValidationMessage(
                        $t(errorMessage)
                    );
                }
            });
            return false;
        },

        /**
         * @return {*}
         */
        getPlaceOrderDeferredObject: function () {
            var self = this;
            return $.when(
                placeOrderAction(this.getData(), self.messageContainer)
            );
        },

        /**
         * Get payment information
         * @returns {Boolean}
         */
        getPaymentInfo: function() {
            var cardHolderName = $("#instapay_cc_name").val();
            var cardNumber = $("#instapay_cc_number").val();
            var expMonth = $("#instapay_expiration").val();
            var expYear = $("#instapay_expiration_yr").val();
            var cvv = $("#instapay_cc_cid").val();
            expMonth = expMonth.toString();
            expMonth = expMonth.length < 2 ? ("0" + expMonth) : expMonth;
            expYear = expYear.substring(expYear.length - 2, expYear.length);
            this.instapayPaymentData['ccname'] = cardHolderName;
            this.instapayPaymentData['ccnum'] = cardNumber;
            this.instapayPaymentData['expmon'] = expMonth;
            this.instapayPaymentData['expyear'] = expYear;
            this.instapayPaymentData['cvv2'] = cvv;

            if (!this.validateCardDetails(cardHolderName, cardNumber, expMonth, expYear, cvv)) {
                return false;
            } else {
                return true;
            }
        },

        /**
         * Validate card details
         * @returns {void}
         */
        validateCardDetails: function(cardHolderName, cardNumber, expMonth, expYear, cvv) {
            var self = this;
            if (cardHolderName == '' || cardNumber == '' || expMonth == '' || expYear == '' || cvv == '') {
                this.errorValidationMessage(
                    $t('Enter the card details and try again.')
                );
                return false;
            } else {
                return true;
            }
        },
    });
});
