define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'doctordogg_stripe_payment',
                component: 'DoctorDogg_Stripe/js/view/payment/method-renderer/doctordogg-stripe-payment'
            }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
