<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Processor;

use \Magento\Authorization\Model\UserContextInterface;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Customer\Api\Data\CustomerInterface;
use \Magento\Checkout\Model\Session;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\App\Action\HttpGetActionInterface;
use \Magento\Framework\Controller\Result\Json;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Psr\Log\LoggerInterface;
use \Stripe\StripeClient;
use \Stripe\Exception\ApiErrorException;
use \DoctorDogg\Stripe\Api\ConfigReaderInterface;
use \DoctorDogg\Stripe\Model\StripeClientFactory;
use \DoctorDogg\Stripe\Model\Processor\GetStripePaidResultInterface;
use \DoctorDogg\Stripe\Model\Stripe\Customer\ManagementInterface as CustomerManagementInterface;
use \DoctorDogg\Stripe\Model\Stripe\Product\ManagementInterface as ProductManagementInterface;

/**
 * Try to auto charge from the customer card.
 */
class GetStripePaidResult implements GetStripePaidResultInterface
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
     * @var ConfigReaderInterface
     */
    private ConfigReaderInterface $configReaderInterface;

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
     * @param ConfigReaderInterface $configReaderInterface
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
        ConfigReaderInterface $configReaderInterface,
        CustomerManagementInterface $customerManagementInterface,
        ProductManagementInterface $productManagementInterface,
        LoggerInterface $logger
    ) {
        $this->userContext = $userContext;
        $this->jsonFactory = $jsonFactory;
        $this->stripeClientFactory = $stripeClientFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->checkoutSession = $checkoutSession;
        $this->configReaderInterface = $configReaderInterface;
        $this->customerManagementInterface = $customerManagementInterface;
        $this->productManagementInterface = $productManagementInterface;
        $this->logger = $logger;
    }

    /**
     * Try to make auto payment from the client card.
     *
     * @param RequestInterface $request
     * @return ResultInterface
     */
    public function pay(RequestInterface $request): ResultInterface
    {
        /**
         * @var Json $resultJson
         */
        $resultJson = $this->jsonFactory->create();

        if (!$request->isAjax()) {}

        try {
            $customerId = (int) $this->userContext->getUserId();
            if (!$customerId) {
                throw new \Exception('Customer is not logined.');
            }

            $quote = $this->checkoutSession->getQuote();

            $items = $quote->getItems();
            $itemsCount = $quote->getItemsCount();

            if (!\is_iterable($items) || !$itemsCount) {
                throw new \Exception('Cart is empty.');
            }


            $apiKey = $this->configReaderInterface->getStripeApiKey();
            /**
             * @var StripeClient $stripe
             */
            $stripe = $this->stripeClientFactory->create(
                [
                    'config' => $apiKey
                ]
            );

            /**
             * @var CustomerInterface $customerMagento
             */
            $customerMagento = $this->customerRepositoryInterface->getById($customerId);

            $customerStripe = $this->customerManagementInterface->getStripeCustomerByMagentoCustomer($customerMagento);

            if (!$customerStripe) {
                throw new \Exception('Customer Stripe does not exist.');
            }

            $customerStripe = $this->customerManagementInterface->setDefaultPaymentSource($customerStripe);

            /**
             * Create invoice.
             */
            $invoice = $stripe->invoices->create([
                'customer' => $customerStripe->id,
                'collection_method' => 'charge_automatically',

//                'currency ' => 'usd',//@TODO: Does not work
            ]);

            $invoiceItems = $this->addItemsToInvoice($items, (string)$customerStripe->id,(string)$invoice->id);

            /**
             * Pay for invoice automatically.
             */
            $payedInvoice = $stripe->invoices->pay($invoice->id, []);

        } catch (\Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            $resultJson->setData([
                'error' => 1,
                'message' => $throwable->getMessage(),
            ]);
            return $resultJson;
        }

        $resultJson->setData([
            'paid' => $payedInvoice->paid ?? null,
        ]);

        return $resultJson;
    }

    /**
     * Add items to invoice.
     *
     * @param iterable $items
     * @param string $customerStripeId
     * @param string $invoiceId
     * @return array
     * @throws ApiErrorException
     */
    private function addItemsToInvoice(Iterable $items, string $customerStripeId, string $invoiceId): array
    {
        $priceType = 'one_time';

        /**
         * @var StripeClient $stripe
         */
        $stripe = $this->stripeClientFactory->create(
            [
                'config' => $this->configReaderInterface->getStripeApiKey()
            ]
        );

        $invoiceItems = [];
        foreach($items as $item) {
            $existingStripePriceId =
                $this->productManagementInterface->getPriceIdByProductSku($item->getSku(), $priceType);
            /**
             * If we have no such product in Stripe with this ID let's create it.
             */
            if (!$existingStripePriceId) {
                $existingStripePriceId = $this->productManagementInterface
                    ->getProductPriceId(
                        (string)$item->getName(),
                        (float)$item->getPrice(),
                        (string)$item->getSku(),
                        $priceType
                    );
            }

            $invoiceItems[] = $stripe->invoiceItems->create([
                'customer' => $customerStripeId,
                'price' => $existingStripePriceId,
                'invoice' => $invoiceId,
                'quantity' => (float)$item->getQty(),
            ]);
        }

        return $invoiceItems;
    }
}
