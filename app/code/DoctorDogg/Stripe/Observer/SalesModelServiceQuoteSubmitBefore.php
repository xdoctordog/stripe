<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Psr\Log\LoggerInterface;
use \DoctorDogg\LogMessagePreparer\Api\LogMessagePreparerInterface;

/**
 * Observer to move the custom fields from the quote to the newly created order.
 */
class SalesModelServiceQuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var LogMessagePreparerInterface
     */
    private LogMessagePreparerInterface $ogMessagePreparerInterface;

    /**
     * Constructor.
     *
     * @param LogMessagePreparerInterface $ogMessagePreparerInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        LogMessagePreparerInterface $ogMessagePreparerInterface,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->ogMessagePreparerInterface = $ogMessagePreparerInterface;
    }

    /**
     * Set delivery date to order from quote delivery date
     *
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        try {
            $observer
                ->getEvent()
                ->getOrder()
                ->setDoctordoggStripeInvoiceId(
                    $observer->getEvent()->getQuote()->getDoctordoggStripeInvoiceId()
                );
        } catch (\Throwable $throwable) {
            $this->logger->info($this->ogMessagePreparerInterface->getErrorMessage($throwable));
        }

        try {
            $observer
                ->getEvent()
                ->getOrder()
                ->setDoctordoggStripePaidStatus(
                    $observer->getEvent()->getQuote()->getDoctordoggStripePaidStatus()
                );
        } catch (\Throwable $throwable) {
            $this->logger->info($this->ogMessagePreparerInterface->getErrorMessage($throwable));
        }
        return $this;
    }
}
