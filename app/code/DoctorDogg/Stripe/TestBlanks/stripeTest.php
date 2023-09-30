<?php


/**
 * @TODO: DOCTORDOGG DID IT
 */
$debug = $_GET['debug'] ?? null;
$debug = null;
if ($debug === 'true') {
    /**
     * Test: Saving the validation errors when buffer product is not valid.
     */
    try {
        //DOCTORDOGG DEBUG HTTP
        $objectManager = $this->_objectManager;

        /**
         * @var \Stripe\StripeClientFactory $stripeClientFactory
         */
        $stripeClientFactory = $objectManager->create(
            \Stripe\StripeClientFactory::class
        );

        /**
         * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
         */
        $customerRepository = $objectManager->create(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $customer = $customerRepository->getById(2);
        $customerStripeId = $customer->getCustomAttributes()['doctordogg_customer_stripe_id']->getValue() ?? null;

        $stripe = $stripeClientFactory->create(
            [
                'config' =>
                    "sk_test_51NrTuuH2QR7AyPBtIabmM1nGiWVixzqjyaLslCUL0Pq9pOwT2LVv9k87S3e9L3MnwnWzZLgT8Shnx4K7bU7POHTD00X7cZcgd2"
            ]
        );

        $products = $stripe->products->all(['limit' => 9999]);

        /**
         * Getting our customer by Customer Stripe ID.
         * 'cus_Of45zedM7Ne2iM',// Cute alpaca
         * 'cus_OfnFBWjrLzxfNd',// Dzmitry Marozau
         */
        $customer = $stripe->customers->retrieve($customerStripeId, []);

        $defaultPaymentMethod = null;
        $invoiceSettings = $customer->invoice_settings;
        if ($invoiceSettings) {
            $defaultPaymentMethod = $invoiceSettings->default_payment_method ?? null;
        }

        $defaultPaymentMethod = null;
        /**
         * Check if the customer has empty `default_payment_method` field,
         * Try to attach the existing payment method to it.
         */
        if(!$defaultPaymentMethod) {
            /**
             * @ATTENTION!: Card should be attached manually to the account inside admin area of Stripe account.
             *
             * Get all customer's payment methods (cards).
             */
            $paymentMethods = $stripe->paymentMethods->all(
                [
                    'customer' => $customer->id,
                ]
            );

            $allPaymentMentods = $paymentMethods->data ?? null;
            if (\is_iterable($allPaymentMentods)) {
                $firstPaymentMentod = $allPaymentMentods[0] ?? null;
                if ($firstPaymentMentod) {
                    $firstPaymentMentodId = $firstPaymentMentod->id ?? null;
                }
            }

            if ($firstPaymentMentodId) {
                /**
                 * Set the existing card of the customer as the default payment method for the invoices.
                 */
                $updCustomer = $stripe->customers->update(
                    $customer->id,
                    [
                        'invoice_settings' =>
                            [
                                'default_payment_method' => $firstPaymentMentodId
                            ]
                    ]
                );
            }
        }

        /**
         * Create invoice.
         */
        $invoice = $stripe->invoices->create([
            'customer' => $customer->id,
            'collection_method' => 'charge_automatically',
        ]);

        $payedInvoice = $stripe->invoices->pay($invoice->id, []);

        throw new \Exception('REMOVE LOCAL DEBUG');

        /**
         * Does not work
         *

        $result = $stripe->paymentMethods->attach(
        'pm_1NsoHmH2QR7AyPBtym6mprnF',
        [
        'customer' => $customer->id,
        ]
        );
         */

//                $checkoutSession = $stripe->checkout->sessions->create([
//                    'payment_method_types' => ['card'],
//                    'mode' => 'setup',
//                    'customer' => $customer->id,
//                    'success_url' => 'https://magento.loc/success',
//                    'cancel_url' => 'https://magento.loc/cancel',
//                ]);
//
//                $setUpIntentId = $checkoutSession->setup_intent ?? null;
//
//                $setUpIntent = $stripe->setupIntents->retrieve($setUpIntentId, []);
//
//                $invoices = $stripe->invoices->all(
//                    [
//                        'status' => 'paid',
//                        "customer" => "cus_Of5rB6ybdTkhCw",
//                    ]
//                );
//
//
//                $customer = $stripe->customers->retrieve(
//                    'cus_Of45zedM7Ne2iM',
//                    []
//                );

//                $customers = $stripe->customers->all(['limit' => 999]);
//                foreach ($customers->data as $customer) {
//                    $result = $stripe->customers->delete(
//                        $customer->id,
//                        []
//                    );
//                }
//


        $product = $stripe->products->create([
            'name' => 'Doctor Dogg Subscription',
            'description' => '$7 / Month subscription',
        ]);
        $price = $stripe->prices->create([
            'unit_amount' => 1200,
            'currency' => 'usd',
            'recurring' => ['interval' => 'month'],
            'product' => $product->id,
        ]);

        // Create a Checkout Session
        $checkoutSession = $stripe->checkout->sessions->create([
            'line_items' => [
                [
                    'price' => $price->id,
                    'quantity' => 1,
                ],
            ],
            'mode' => 'subscription',
            'success_url' => 'https://magento.loc/success',
            'cancel_url' => 'https://magento.loc/cancel',
            'payment_method_types' => ['card'],
        ]);
        $checkoutSessionUrl = $checkoutSession->url;

//                // Create a coupon
//                $coupon = $stripe->coupons->create([
//                    'percent_off' => 25,
//                    'duration' => 'repeating',
//                    'duration_in_months' => 3,
//                ]);
//
//                // Confirm a PaymentIntent
//                $paymentIntent = $stripe->paymentIntents->confirm(
//                    'pi_1DeQ7b2eZvKYlo2C5FUypnEA',
//                    ['payment_method' => 'pm_card_visa']
//                );

//                // Retrieve a Checkout Session and expand line_items
//                $checkoutSession = $stripe->checkout->sessions->retrieve(
//                    'cs_test_123',
//                    [
//                        'expand' => ['line_items'],
//                    ]
//                );
//

//                $price = $stripe->prices->create([
//                    'unit_amount' => 1200,
//                    'currency' => 'usd',
//                    'recurring' => ['interval' => 'month'],
//                    'product' => $product->id,
//                ]);
//
//                $paymentIntent = $stripe->paymentIntents->create([
//                    'amount' => 500,
//                    'currency' => 'gbp',
//                    'payment_method' => 'pm_card_visa',
//                ]);
//                $paymentIntent->confirm();
//                $paymentIntent->capture();

        $a = 10;
    } catch (\Throwable $throwable) {
        $a = 10;
    }
    throw new \Exception('REMOVE LOCAL DEBUG');
}
