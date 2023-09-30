<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Stripe\Customer;

use \Magento\Customer\Api\Data\CustomerInterface;
use \Stripe\Customer as StripeCustomer;
use \Stripe\Exception\ApiErrorException;
use \DoctorDogg\Stripe\Model\Stripe\ApiConnection;
use \DoctorDogg\Stripe\Model\Stripe\Customer\ManagementInterface;

/**
 * Manager for Stripe Customer.
 */
class Management extends ApiConnection implements ManagementInterface
{
    /**
     * Get Stripe customer by Magento customer.
     *
     * @param CustomerInterface $customerMagento
     * @return StripeCustomer|null
     * @throws ApiErrorException
     */
    public function getStripeCustomerByMagentoCustomer(CustomerInterface $customerMagento): StripeCustomer|null
    {
        try {
            /**
             * @TODO: SHOULD BE CONST `doctordogg_customer_stripe_id`
             */
            $customerStripeIdObject = $customerMagento->getCustomAttributes()['doctordogg_customer_stripe_id'] ?? null;

            if (!\is_object($customerStripeIdObject)) {
                throw new \Exception('Stripe customer is not defined.');
            }

            $customerStripeId = $customerStripeIdObject->getValue();

            if (!$customerStripeId) {
                throw new \Exception('Stripe Customer is not defined.');
            }

            $customerStripe = $this->stripe->customers->retrieve($customerStripeId, []);
            if (!$customerStripe->id) {
                throw new \Exception('Stripe Customer is not defined.');
            }
        } catch (\Throwable $throwable) {
            /**
             * If something is wrong, so we can't get the Stripe customer by the "Stripe Customer ID",
             * so we should try to get/(create and get) the Stripe customer by the email
             */

            $customerStripe = $this->getByEmailAndCreate($customerMagento);
        }

        return $customerStripe;
    }

    /**
     * Check if customer exists in Stripe. Return existing or create the new one and return.
     *
     * @param CustomerInterface $customerMagento
     * @return StripeCustomer|null
     */
    public function getByEmailAndCreate(CustomerInterface $customerMagento): StripeCustomer|null
    {
        $customerStripe = null;
        try {
            $customerStripe = $this->getByEmail($customerMagento->getEmail());
            if (!$customerStripe || !($customerStripe instanceof StripeCustomer)) {
                $customerStripe = $this->stripe->customers->create([
                    'email' => $customerMagento->getEmail(),
                    'name' => $customerMagento->getFirstname() . ' ' . $customerMagento->getMiddlename() . ' ' . $customerMagento->getLastname(),
                    'description' => 'Magento customer',
                ]);
            }
        } catch (\Throwable $throwable) {
            $this->logger->error($this->logMessagePreparerInterface->getErrorMessage($throwable));
        }

        return $customerStripe;
    }

    /**
     * Get customer by email.
     *
     * @param string $customerEmail
     * @return StripeCustomer|null
     */
    public function getByEmail(string $customerEmail): StripeCustomer|null
    {
        try {
            $customers = $this->stripe->customers->all(
                [
                    'email' => $customerEmail,
                    'limit' => 1
                ]
            );
            foreach ($customers as $customer) {
                /**
                 * Not necessary.
                 */
                $email = $customer->email;
                if ($email === $customerEmail) {
                    return $customer;
                }
            }
        } catch (\Throwable $throwable) {
            $this->logger->error($this->logMessagePreparerInterface->getErrorMessage($throwable));
        }

        return null;
    }

    /**
     * Try to auto set the default payment method for the Stripe customer.
     *
     * @ATTENTION! Customer should have at least one added card to his Stripe account.
     *
     * @param StripeCustomer $customerStripe
     * @return StripeCustomer
     * @throws ApiErrorException
     */
    public function setDefaultPaymentSource(StripeCustomer $customerStripe): StripeCustomer
    {
        $defaultPaymentMethod = null;
        $invoiceSettings = $customerStripe->invoice_settings ?? null;
        if ($invoiceSettings) {
            $defaultPaymentMethod = $invoiceSettings->default_payment_method ?? null;
        }

        /**
         * Check if the customer has empty `default_payment_method` field,
         * Try to attach the existing payment method to it.
         */
        if(!$defaultPaymentMethod) {
            /**
             * Get all customer's payment methods (cards).
             */
            $paymentMethods = $this->stripe->paymentMethods->all(
                [
                    'customer' => $customerStripe->id ?? '',
                ]
            );

            $allPaymentMethods = $paymentMethods->data ?? null;
            if (!\is_iterable($allPaymentMethods)) {
                throw new \Exception('Stripe Customer has no payment methods to use.');
            }

            $firstPaymentMethod = $allPaymentMethods[0] ?? null;
            if (!$firstPaymentMethod) {
                throw new \Exception('Stripe Customer has no first payment method in list to use.');
            }

            $firstPaymentMethodId = $firstPaymentMethod->id ?? null;
            if (!$firstPaymentMethodId) {
                throw new \Exception('Stripe Customer has no first payment method ID to use.');
            }

            /**
             * Set the existing card of the customer as the default payment method for the invoices.
             */
            $updatedCustomerStripe = $this->stripe->customers->update(
                $customerStripe->id,
                [
                    'invoice_settings' =>
                        [
                            'default_payment_method' => $firstPaymentMethodId
                        ]
                ]
            );
        }

        return $updatedCustomerStripe ?? $customerStripe;
    }
}
