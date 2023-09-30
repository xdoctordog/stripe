<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Stripe\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Stripe\Customer as StripeCustomer;
use Stripe\Exception\ApiErrorException;

/**
 * Interface for Stripe Customer Manager.
 */
interface ManagementInterface
{
    /**
     * Get customer by email.
     *
     * @param CustomerInterface $customerMagento
     * @return StripeCustomer|null
     */
    public function getByEmailAndCreate(CustomerInterface $customerMagento): StripeCustomer|null;

    /**
     * Get customer by email.
     *
     * @param string $customerEmail
     * @return StripeCustomer|null
     */
    public function getByEmail(string $customerEmail): StripeCustomer|null;

    /**
     * Set default payment source.
     *
     * @param StripeCustomer $customerStripe
     * @return StripeCustomer
     * @throws ApiErrorException
     */
    public function setDefaultPaymentSource(StripeCustomer $customerStripe): StripeCustomer;
}
