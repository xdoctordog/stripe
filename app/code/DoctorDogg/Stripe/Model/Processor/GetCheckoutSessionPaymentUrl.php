<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Processor;

use \Magento\Authorization\Model\UserContextInterface;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Checkout\Model\Session;
use Magento\Customer\Api\Data\CustomerInterface;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\Controller\Result\Json;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Quote\Api\CartRepositoryInterface;
use \Psr\Log\LoggerInterface;
use \Stripe\Customer as StripeCustomer;
use \Stripe\Checkout\Session as StripeSession;
use \DoctorDogg\Stripe\Model\StripeClientFactory;
use \DoctorDogg\Stripe\Api\ConfigReaderInterface;
use \DoctorDogg\Stripe\Api\GetSessionForCustomerAndLineItemsInterface;
use \DoctorDogg\Stripe\Model\Processor\GetCheckoutSessionPaymentUrlInterface;
use \DoctorDogg\Stripe\Model\Stripe\Customer\ManagementInterface as CustomerManagementInterface;
use \DoctorDogg\Stripe\Model\Stripe\Product\ManagementInterface as ProductManagementInterface;

/**
 * Class which gets the payment link to the Stripe form.
 */
class GetCheckoutSessionPaymentUrl implements GetCheckoutSessionPaymentUrlInterface
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
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepositoryInterface;

    /**
     * @var ConfigReaderInterface
     */
    private ConfigReaderInterface $configReaderInterface;

    /**
     * @var GetSessionForCustomerAndLineItemsInterface
     */
    private GetSessionForCustomerAndLineItemsInterface $getSessionForCustomerAndLineItemsInterface;

    /**
     * @var CustomerManagementInterface
     */
    private CustomerManagementInterface $customerManagementInterface;

    /**
     * @var ProductManagementInterface
     */
    private ProductManagementInterface $productManagementInterface;

    /**
     * Constructor.
     *
     * @param UserContextInterface $userContext
     * @param JsonFactory $jsonFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param StripeClientFactory $stripeClientFactory
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $cartRepositoryInterface
     * @param ConfigReaderInterface $configReaderInterface
     * @param GetSessionForCustomerAndLineItemsInterface $getSessionForCustomerAndLineItemsInterface
     * @param CustomerManagementInterface $customerManagementInterface
     * @param ProductManagementInterface $productManagementInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        UserContextInterface $userContext,
        JsonFactory $jsonFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        StripeClientFactory $stripeClientFactory,
        Session $checkoutSession,
        CartRepositoryInterface $cartRepositoryInterface,
        ConfigReaderInterface $configReaderInterface,
        GetSessionForCustomerAndLineItemsInterface $getSessionForCustomerAndLineItemsInterface,
        CustomerManagementInterface $customerManagementInterface,
        ProductManagementInterface $productManagementInterface,
        LoggerInterface $logger
    ) {
        $this->userContext = $userContext;
        $this->jsonFactory = $jsonFactory;
        $this->stripeClientFactory = $stripeClientFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->configReaderInterface = $configReaderInterface;
        $this->getSessionForCustomerAndLineItemsInterface = $getSessionForCustomerAndLineItemsInterface;
        $this->customerManagementInterface = $customerManagementInterface;
        $this->productManagementInterface = $productManagementInterface;
        $this->logger = $logger;
    }

    /**
     * Process the request.
     *
     * @param RequestInterface $request
     * @return ResultInterface
     */
    public function process(RequestInterface $request): ResultInterface
    {
        /**
         * @var Json $resultJson
         */
        $resultJson = $this->jsonFactory->create();
        if (!$request->isAjax()) {}
        $customerId = $this->userContext->getUserId();
        if ($customerId === null) {
            $resultJson->setData([
                'error' => 1,
                'message' => 'User is not logined.',
            ]);

            return $resultJson;
        }

        $quote = null;
        try {
            $quote = $this->checkoutSession->getQuote();
        } catch (\Throwable $throwable) {
            /**
             * No actions when we don't have quote.
             */
        }

        if (!$quote || !$quote->getEntityId()) {
            $resultJson->setData([
                'error' => 1,
                'message' => 'User does not have active cart.',
            ]);

            return $resultJson;
        }

        $stripeInvoiceId = $quote->getDoctordoggStripeInvoiceId();
        $paid = $quote->getDoctordoggStripePaidStatus();

        if ($paid && $stripeInvoiceId) {
            $resultJson->setData([
                'paid' => $paid
            ]);

            return $resultJson;
        }

        $items = $quote->getItems();
        if (!\is_iterable($items)) {
            $resultJson->setData([
                'error' => 1,
                'message' => 'Cart is empty',
            ]);
            $this->logger->error('Cart is empty');
            return $resultJson;
        }

        try {
            $customerMagento = $this->customerRepositoryInterface->getById($customerId);

            /**
             * @var StripeCustomer $customerStripe
             */
            $customerStripe = $this->customerManagementInterface->getStripeCustomerByMagentoCustomer($customerMagento);

            $lineItems = [];
            foreach ($items as $item) {
                $sku = ($item->getSku()) ? (string)$item->getSku() : '';

                $priceType = 'recurring';
                $existingStripePriceId = $this->productManagementInterface->getPriceIdByProductSku($sku, $priceType);

                /**
                 * If we have no such product in Stripe with this ID let's create it.
                 */
                if (!$existingStripePriceId) {
                    $existingStripePriceId = $this->productManagementInterface
                        ->getProductPriceId(
                            (string)$item->getName(),
                            (float)$item->getPrice(),
                            (string)$item->getSku(),
                            $type = 'recurring'
                        );
                }

                $lineItems[] = [
                    'price' => $existingStripePriceId,
                    'quantity' => $item->getQty(),
                ];
            }

            /**
             * @var StripeSession $checkoutSession
             */
            $checkoutSession = $this->getSessionForCustomerAndLineItemsInterface
                ->getSession((string)$customerStripe->id, $lineItems);

            $checkoutSessionId = $checkoutSession->id;
            $quote->setDoctordoggStripeCheckoutSessionId($checkoutSessionId);

            $this->cartRepositoryInterface->save($quote);

            $checkoutSessionUrl = $checkoutSession->url ?? '';
        } catch (\Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            $resultJson->setData([
                'error' => 1,
                'message' => 'Unable to process the request',
            ]);
            return $resultJson;
        }

        $resultJson->setData([
            'checkout_session_url' => $checkoutSessionUrl
        ]);

        return $resultJson;
    }
}
