<?php
if (false || $debug === 'true') {
    /**
     * Test: Saving the validation errors when buffer product is not valid.
     */
    try {
        //DOCTORDOGG DEBUG HTTP
        $objectManager = $this->_objectManager;

        /**
         * @var \DoctorDogg\Stripe\Api\Data\CartInterfaceFactory $cartFactory
         */
        $cartFactory = $objectManager->create(
            \DoctorDogg\Stripe\Api\Data\CartInterfaceFactory::class
        );

        /**
         * @var \Magento\Quote\Api\CartRepositoryInterface $cartRepository
         */
        $cartRepository = $objectManager->create(
            \Magento\Quote\Api\CartRepositoryInterface::class
        );

        $stripeCheckoutSessionId = 'cs_test_a1OcuL4KgJm0HfBW10WEiPFRTxSdRhb4squZdpS5Df8guMhNju1i2alov3';
        $cart = $cartFactory->create()->loadByStripeCheckoutSessionId($stripeCheckoutSessionId);

        $cart->setDoctordoggStripeInvoiceId('UPD__UPD');

        $cartRepository->save($cart);

        $a = 10;
    } catch (\Throwable $throwable) {
        $a = 10;
    }
    throw new \Exception('REMOVE LOCAL DEBUG');
}
