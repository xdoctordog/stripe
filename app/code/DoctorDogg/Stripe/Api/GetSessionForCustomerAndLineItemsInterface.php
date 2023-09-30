<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Api;

use \Stripe\Checkout\Session;

/**
 * Interface which provides the possibility to create session for Stripe customer with line items.
 */
interface GetSessionForCustomerAndLineItemsInterface
{
    /**
     * Get session.
     *
     * @param string $customerStripeId
     * @param array $lineItems
     * @return Session
     */
    public function getSession(string $customerStripeId, array $lineItems): Session;
}
