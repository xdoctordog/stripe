<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Webhook\Handler\Checkout\Session;

use \Magento\Quote\Api\CartRepositoryInterface;
use \Psr\Log\LoggerInterface;
use \DoctorDogg\Stripe\Api\WebhookHandlerInterface;
use \DoctorDogg\LogMessagePreparer\Api\LogMessagePreparerInterface;
use \DoctorDogg\Stripe\Api\Data\CartInterfaceFactory;

/**
 * Handler for type: checkout.session.completed
 */
class Completed implements WebhookHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var LogMessagePreparerInterface
     */
    private LogMessagePreparerInterface $logMessagePreparerInterface;

    /**
     * @var CartInterfaceFactory
     */
    private CartInterfaceFactory $cartInterfaceFactory;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepositoryInterface;

    /**
     * Constructor.
     *
     * @param CartRepositoryInterface $cartRepositoryInterface
     * @param CartInterfaceFactory $cartInterfaceFactory
     * @param LoggerInterface $logger
     * @param LogMessagePreparerInterface $logMessagePreparerInterface
     */
    public function __construct(
        CartRepositoryInterface $cartRepositoryInterface,
        CartInterfaceFactory $cartInterfaceFactory,
        LoggerInterface $logger,
        LogMessagePreparerInterface $logMessagePreparerInterface
    ) {
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartInterfaceFactory = $cartInterfaceFactory;
        $this->logger = $logger;
        $this->logMessagePreparerInterface = $logMessagePreparerInterface;
    }

    /**
     * Handle.
     *
     * @param array $webhookData
     * @return void
     */
    public function handle(array $webhookData)
    {
        $stripeCheckoutSessionId = $webhookData['data']['object']['id'] ?? null;

        if (!$stripeCheckoutSessionId) {
            return;
        }
        $stripeInvoiceId = $webhookData['data']['object']['invoice'] ?? null;

        try {
            $quote = $this->cartInterfaceFactory->create()->loadByStripeCheckoutSessionId($stripeCheckoutSessionId);
            if (!$quote->getId()) {
                throw new \Exception('Quote for Stripe Checkout Session ID ' . $stripeCheckoutSessionId . ' is not found');
            }
            $quote->setDoctordoggStripeInvoiceId($stripeInvoiceId);
            $this->cartRepositoryInterface->save($quote);
        } catch (\Throwable $throwable) {
            $this->logger->notice($this->logMessagePreparerInterface->getErrorMessage($throwable));
        }
    }
}
