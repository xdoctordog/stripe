<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Api;

/**
 * Interface for processing the webhook data.
 */
interface WebhookHandlerInterface
{
    /**
     * Handle the incoming webhook.
     *
     * @param array $webhookData
     * @return void
     */
    public function handle(array $webhookData);
}
