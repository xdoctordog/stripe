<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Stripe\Session;

use \DoctorDogg\Stripe\Api\GetSessionForCustomerAndLineItemsInterface;
use \DoctorDogg\Stripe\Model\Stripe\ApiConnection;
use \Stripe\Checkout\Session;

class GetSessionForCustomerAndLineItems extends ApiConnection implements GetSessionForCustomerAndLineItemsInterface
{
    /**
     * Get session.
     *
     * @param string $customerStripeId
     * @param array $lineItems
     * @return Session
     */
    public function getSession(string $customerStripeId, array $lineItems): Session
    {
        $checkoutSession = null;
        try {
            $checkoutSession = $this->stripe->checkout->sessions->create([
                'line_items' => $lineItems,
                'customer' => $customerStripeId,
                'mode' => 'subscription',
    //            'mode' => 'setup',
                /**
                 * @TODO: Should be the real addresses of the web stores.
                 *        Probably we should use Magento admin config value with store url.
                 */
                'success_url' => 'https://magento.loc/checkout/#payment',
                'cancel_url' => 'https://magento.loc/cancel',
                'payment_method_types' => ['card'],
            ]);
        } catch (\Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
        }

        return $checkoutSession;
    }
}
