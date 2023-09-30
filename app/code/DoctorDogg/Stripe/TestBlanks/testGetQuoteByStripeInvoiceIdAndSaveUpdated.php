<?php
if (true) {
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

        $stripeInvoiceId = 'LSAFJHDLSK';
        $cart = $cartFactory->create()->loadByStripeInvoiceId($stripeInvoiceId);

        $cart->setDoctordoggStripeInvoiceId('UPD_' . $stripeInvoiceId . '_UPD');

        $cartRepository->save($cart);

        $a = 10;
    } catch (\Throwable $throwable) {
        $a = 10;
    }
    throw new \Exception('REMOVE LOCAL DEBUG');
}
