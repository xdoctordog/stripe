define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'mage/url',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, $, mageUrl, quote) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'DoctorDogg_Stripe/payment/doctordogg-stripe-template',
                getSessionUrlUrl: '/doctordogg_stripe/stripe_checkout_sessionurl/get/',
                payUrl: '/doctordogg_stripe/stripe_checkout_invoice/pay/',
            },

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super();

                var stripeInvoiceId = window.checkoutConfig.quoteData.doctordogg_stripe_invoice_id;
                var stripePaidStatus = window.checkoutConfig.quoteData.doctordogg_stripe_paid_status;

                console.log('stripeInvoiceId: ' + stripeInvoiceId);
                console.log('stripePaidStatus: ' + stripePaidStatus);

                var isChecked = this.isChecked();
                console.log('isChecked: ' + isChecked);

                if (stripePaidStatus === '1') {
                    if (isChecked === 'doctordogg_stripe_payment') {
                        this.placeOrder();
                    }
                }

                return this;
            },

            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.customtemplate.mailingAddress;
            },

            /**
             * Flow for the 'subscription' payment.
             */
            getAndShowStripePopUp: function() {
                this._sendAjax();
            },

            /**
             * Flow for the 'invoice' payment.
             */
            tryAutoChargeClientCard: function() {
                this._payInvoiceSendAjax();
            },

            /**
             * Test trigger of the webhook handler.
             */
            triggerWebhookHandler: function() {
                this._sendAjaxWebHookHandler();
            },

            /**
             * @TODO: This is only for test the POST request to the webhook handler.
             *
             * @private
             */
            _sendAjaxWebHookHandler: function () {
                const url = mageUrl.build('/doctordogg_stripe/stripe_webhook/handler/');
                const sendData = {
                    someKey: 'someValue',
                };
                $.ajax({
                    url: url,
                    data: sendData,
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,
                    contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                    processData: true,

                    /**
                     * @callback
                     */
                    success: $.proxy(function (response) {
                        console.log(response);
                    }, this)
                });
            },

            /**
             * Send ajax to pay for Stripe invoice.
             *
             * @private
             */
            _payInvoiceSendAjax: function () {
                const url = mageUrl.build(this.payUrl);
                const sendData = {};
                $.ajax({
                    url: url,
                    data: sendData,
                    type: 'get',
                    dataType: 'json',
                    showLoader: true,
                    contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                    processData: true,

                    /**
                     * @callback
                     */
                    success: $.proxy(function (response) {
                        let paid = response.paid;
                        console.log('paid: ' + paid);
                        if (paid) {
                            this.placeOrder();
                        } else {
                            console.log('Error:');
                        }
                    }, this)
                });
            },

            /**
             * Send ajax to get Stripe link for payment.
             *
             * @private
             */
            _sendAjax: function () {
                const url = mageUrl.build(this.getSessionUrlUrl);
                const sendData = {};
                $.ajax({
                    url: url,
                    data: sendData,
                    type: 'get',
                    dataType: 'json',
                    showLoader: true,
                    contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                    processData: true,

                    /**
                     * @callback
                     */
                    success: $.proxy(function (response) {
                        let paid = response.paid;
                        if (paid) {
                            this.placeOrder();

                            return;
                        }
                        let stripeUrl = response.checkout_session_url;
                        console.log(stripeUrl);
                        if (stripeUrl) {
                            window.open(stripeUrl);
                        } else {
                            console.log('Error:');
                        }
                    }, this)
                });
            },
        });
    }
);
