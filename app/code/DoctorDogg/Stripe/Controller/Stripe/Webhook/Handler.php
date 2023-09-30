<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Controller\Stripe\Webhook;

use \Magento\Authorization\Model\UserContextInterface;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Checkout\Model\Session;
use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Action\HttpGetActionInterface;
use \Magento\Framework\App\Action\HttpPostActionInterface;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\Controller\Result\Json;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Psr\Log\LoggerInterface;
use \Stripe\StripeClient;
use \DoctorDogg\Stripe\Model\StripeClientFactory;
use \DoctorDogg\Stripe\Model\Webhook\Handler as WebhookHandler;

/**
 * Webhook handler.
 * @route: /doctordogg_stripe/stripe_webhook/handler/
 */
class Handler extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var StripeClientFactory
     */
    private StripeClientFactory $stripeClientFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepositoryInterface;

    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var WebhookHandler
     */
    private WebhookHandler $webhookHandler;

    /**
     * Constructor.
     *
     * @param WebhookHandler $webhookHandler
     * @param UserContextInterface $userContext
     * @param JsonFactory $jsonFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param StripeClientFactory $stripeClientFactory
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        WebhookHandler $webhookHandler,
        UserContextInterface $userContext,
        JsonFactory $jsonFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        StripeClientFactory $stripeClientFactory,
        Session $checkoutSession,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->webhookHandler = $webhookHandler;
        $this->userContext = $userContext;
        $this->jsonFactory = $jsonFactory;
        $this->stripeClientFactory = $stripeClientFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        parent::__construct(
            $context
        );
    }

    /**
     * Process webhook.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /**
         * @var Json $resultJson
         */
        $resultJson = $this->jsonFactory->create();
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;

        $this->webhookHandler->handle();

        $resultJson->setData([
            'Request method' => $requestMethod,
        ]);

        return $resultJson;
    }
}
