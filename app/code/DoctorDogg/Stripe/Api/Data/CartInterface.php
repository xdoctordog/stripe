<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Api\Data;

use \Magento\Framework\Exception\LocalizedException;
use \Magento\Quote\Api\Data\CartInterface as CartInterfaceMagento;

/**
 * This interface just for our own needs. No need to rewrite it entirely.
 */
interface CartInterface extends CartInterfaceMagento
{
    /**
     * @const string STRIPE_INVOICE_ID
     */
    public const STRIPE_CHECKOUT_SESSION_ID = 'doctordogg_stripe_checkout_session_id';

    /**
     * @const string STRIPE_INVOICE_ID
     */
    public const STRIPE_INVOICE_ID = 'doctordogg_stripe_invoice_id';

    /**
     * @const string STRIPE_PAID_STATUS_KEY
     */
    public const STRIPE_PAID_STATUS_KEY = 'doctordogg_stripe_paid_status';

    /**
     * Loading by Stripe invoice ID.
     *
     * @param string $stripeInvoiceId
     * @return $this
     * @throws LocalizedException
     */
    public function loadByStripeInvoiceId(string $stripeInvoiceId): self;

    /**
     * Loading by Stripe invoice ID.
     *
     * @param string $stripeCheckoutSessionId
     * @return $this
     * @throws LocalizedException
     */
    public function loadByStripeCheckoutSessionId(string $stripeCheckoutSessionId): self;
}
