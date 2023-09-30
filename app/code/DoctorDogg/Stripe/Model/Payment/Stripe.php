<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Payment;

use \Magento\Payment\Model\Method\AbstractMethod;

/**
 * Stripe Payment implementation.
 */
class Stripe extends AbstractMethod
{
    /**
     * @cosnt string PAYMENT_METHOD_CUSTOM_INVOICE_CODE
     */
    const STRIPE_METHOD_INVOICE_CODE = 'doctordogg_stripe_payment';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::STRIPE_METHOD_INVOICE_CODE;
}
