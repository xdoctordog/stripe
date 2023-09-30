<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Stripe;

use \Psr\Log\LoggerInterface;
use \Stripe\StripeClient;
use \DoctorDogg\Stripe\Model\StripeClientFactory;
use \DoctorDogg\Stripe\Api\ConfigReaderInterface;
use \DoctorDogg\LogMessagePreparer\Api\LogMessagePreparerInterface;

/**
 * Connection to Stripe api.
 */
class ApiConnection
{
    /**
     * @var StripeClient
     */
    protected StripeClient $stripe;

    /**
     * @var LogMessagePreparerInterface
     */
    protected LogMessagePreparerInterface $logMessagePreparerInterface;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param StripeClientFactory $stripeClientFactory
     * @param ConfigReaderInterface $configReaderInterface
     * @param LoggerInterface $logger
     * @param LogMessagePreparerInterface $logMessagePreparerInterface
     * @throws \Exception
     */
    public function __construct(
        StripeClientFactory $stripeClientFactory,
        ConfigReaderInterface $configReaderInterface,
        LoggerInterface $logger,
        LogMessagePreparerInterface $logMessagePreparerInterface
    ) {
        $stripeApiKey = $configReaderInterface->getStripeApiKey();

        if (!\is_string($stripeApiKey) || \mb_strlen($stripeApiKey) === 0) {
            throw new \Exception('Stripe api key is not defined');
        }

        /**
         * @var StripeClient $stripe
         */
        $this->stripe = $stripeClientFactory->create(
            [
                'config' => $stripeApiKey
            ]
        );
        $stripeObjectId = \spl_object_id($this->stripe);

        $this->logMessagePreparerInterface = $logMessagePreparerInterface;
        $this->logger = $logger;
    }
}
