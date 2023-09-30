<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model;

use \Magento\Framework\ObjectManagerInterface;
use \Stripe\StripeClient;

/**
 * Factory which create singleton instance of StripeClient.
 *
 * Modified Copy-paste of: generated/code/Stripe/StripeClientFactory.php
 */
class StripeClientFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected string $instanceName = '\\Stripe\\StripeClient';

    /**
     * @var StripeClient|null
     */
    protected null|StripeClient $instance = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(ObjectManagerInterface $objectManager) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create StripeClient instance with specified parameters.
     *
     * @param array $data
     * @return StripeClient
     */
    public function create(array $data = []): StripeClient
    {
        if ($this->instance === null) {
            $this->instance = $this->objectManager->create($this->instanceName, $data);
        }
        return $this->instance;
    }
}
