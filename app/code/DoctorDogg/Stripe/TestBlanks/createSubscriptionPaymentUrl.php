<?php

if (true) {
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

        $stripe = $stripeClientFactory->create(['config' => "sk_test_4eC39HqLyjWDarjtT1zdp7dc"]);
        $prices = $stripe->prices->all(['limit' => 999]);

        $product = $stripe->products->create([
            'name' => 'Starter Subscription',
            'description' => '$12/Month subscription',
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
//            'payment_method_types' => ['card'],
        ]);

        $checkoutSessionUrl = $checkoutSession->url;

        $a = 10;
    } catch (\Throwable $throwable) {
        $a = 10;
    }
    throw new \Exception('REMOVE LOCAL DEBUG');
}
