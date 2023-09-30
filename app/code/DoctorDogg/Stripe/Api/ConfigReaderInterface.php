<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Api;

/**
 * Interface which provides the possibility to get the config values from the admin settings.
 */
interface ConfigReaderInterface
{
    /**
     * Get Stripe api key.
     *
     * @return string|null
     */
    public function getStripeApiKey(): ?string;
}
