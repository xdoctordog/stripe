<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Webhook;

use \Magento\Framework\Serialize\Serializer\Json;
use \DoctorDogg\Stripe\Model\Webhook\Handler\Invoice\Paid;
use \DoctorDogg\Stripe\Model\Webhook\Handler\Checkout\Session\Completed;

/**
 * Webhook handler.
 */
class Handler
{
    /**
     * @var Paid
     */
    private Paid $invoicePaidHandler;

    /**
     * @var Completed
     */
    private Completed $checkoutSessionCompletedHandler;

    /**
     * Constructor.
     *
     * @param Paid $invoicePaidHandler
     * @param Completed $checkoutSessionCompletedHandler
     */
    public function __construct(
        Paid $invoicePaidHandler,
        Completed $checkoutSessionCompletedHandler
    ) {
        $this->invoicePaidHandler = $invoicePaidHandler;
        $this->checkoutSessionCompletedHandler = $checkoutSessionCompletedHandler;
    }

    /**
     * Handle.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @TODO: Logging all webhooks to files. Going to implement this functionality for storing into DB.
         */
        if (true) {
            $phpInput = @file_get_contents('php://input');
            if (\mb_strlen($phpInput) > 0) {
                $inputObject = null;
                try {
                    $inputObject = \json_decode($phpInput, true);
                } catch (\Throwable $exception){

                }

                $webhookType = $inputObject['type'] ?? '';

                try {
                    $result = match($webhookType) {
                        'invoice.paid' => $this->invoicePaidHandler->handle($inputObject),
                        'checkout.session.completed' => $this->checkoutSessionCompletedHandler->handle($inputObject),
                        default => null
                    };
                } catch (\Throwable $throwable){

                }

                $time = \microtime();
                $timeParts = \explode(' ', $time);
                $time = ($timeParts[1] ?? '') . '.' . ($timeParts[0] ?? '');
                $fName = '/var/www/html/pub/stripe/' . $time . '_[' . $webhookType . '].txt';
                $rs = \fopen($fName, 'wb');
                \fclose($rs);
                \file_put_contents($fName, $phpInput, FILE_APPEND);
            }
        }
    }
}
