<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Config\Reader;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Serialize\Serializer\Json;
use \Magento\Store\Model\ScopeInterface;
use \Psr\Log\LoggerInterface;
use \DoctorDogg\Stripe\Api\ConfigReaderInterface;
use \DoctorDogg\LogMessagePreparer\Api\LogMessagePreparerInterface;

/**
 * Reader of config values.
 */
class ConfigReader implements ConfigReaderInterface
{
    /**
     * @const string PATH_STRIPE_API_KEY
     */
    public const PATH_STRIPE_API_KEY = 'doctordogg_stripe_settings/stripe_settings/stripe_api_key';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfigInterface;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var LogMessagePreparerInterface
     */
    private LogMessagePreparerInterface $logMessagePreparerInterface;

    /**
     * Constructor.
     *
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param Json $json
     * @param LoggerInterface $logger
     * @param LogMessagePreparerInterface $logMessagePreparerInterface
     */
    public function __construct(
        ScopeConfigInterface $scopeConfigInterface,
        Json $json,
        LoggerInterface $logger,
        LogMessagePreparerInterface $logMessagePreparerInterface
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->json = $json;
        $this->logger = $logger;
        $this->logMessagePreparerInterface = $logMessagePreparerInterface;
    }

    /**
     * Get Stripe api key.
     *
     * @return string|null
     */
    public function getStripeApiKey(): ?string
    {
        $value = $this->scopeConfigInterface->getValue(
            static::PATH_STRIPE_API_KEY,
            ScopeInterface::SCOPE_STORE
        );

        return ($value) ? (string)$value : null;
    }

    /**
     * Get boolean value.
     *
     * @param $value
     * @return bool|null
     */
    private function _getNullBoolean($value)
    {
        if ($value !== '0' && $value !== 0 && $value !== '1' && $value !== 1) {
            $value = null;
        } else {
            $value = (bool)(int)$value;
        }

        return $value;
    }
}
