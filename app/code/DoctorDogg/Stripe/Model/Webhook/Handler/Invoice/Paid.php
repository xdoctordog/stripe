<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Webhook\Handler\Invoice;

use Magento\Quote\Api\CartRepositoryInterface;
use \Psr\Log\LoggerInterface;
use \DoctorDogg\LogMessagePreparer\Api\LogMessagePreparerInterface;
use \DoctorDogg\Stripe\Api\Data\CartInterfaceFactory;
use \DoctorDogg\Stripe\Api\WebhookHandlerInterface;

/**
 * Handler for type: invoice.paid
 */
class Paid implements WebhookHandlerInterface
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
        $stripeInvoiceId = $webhookData['data']['object']['id'] ?? null;

        if (!$stripeInvoiceId) {
            return;
        }
        $invoicePaid = $webhookData['data']['object']['paid'] ?? null;

        try {
            $quote = $this->cartInterfaceFactory->create()->loadByStripeInvoiceId($stripeInvoiceId);
            if (!$quote->getId()) {
                throw new \Exception('Quote for Stripe Invoice ID ' . $stripeInvoiceId . ' is not found');
            }
            $quote->setDoctordoggStripePaidStatus($invoicePaid);
            $this->cartRepositoryInterface->save($quote);
        } catch (\Throwable $throwable) {
            $this->logger->notice($this->logMessagePreparerInterface->getErrorMessage($throwable));
        }
    }
}
