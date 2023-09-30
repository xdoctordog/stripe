<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Stripe\Product;

use \Stripe\Collection as StripeCollection;
use \Stripe\Price as StripePrice;
use \Stripe\Product as StripeProduct;
use \Stripe\Exception\ApiErrorException;
use \DoctorDogg\Stripe\Model\Stripe\Product\ManagementInterface;
use \DoctorDogg\Stripe\Model\Stripe\ApiConnection;

/**
 * Manager for Stripe Product.
 */
class Management extends ApiConnection implements ManagementInterface
{
    /**
     * We use this to prevent double api-call for one request to PHP side.
     * We are storing the existing products in Stripe system.
     *
     * @var StripeProduct[]
     */
    private array $existingStripeProducts = [];

    /**
     * Get product by sku metadata.
     *
     * @param string $sku
     * @return StripeProduct|null
     */
    public function getProductBySkuMetadata(string $sku): ?StripeProduct
    {
        if (isset($this->existingStripeProducts[$sku])) {

            return $this->existingStripeProducts[$sku];
        }

        try {
            /**
             * @ATTENTION!: Should be the one product with unique sku inside Stripe.
             */
            $products = $this->stripe->products->search([
                'query' => 'active:\'true\' AND metadata[\'sku\']:\'' . $sku . '\'',
            ]);

            $productsData = $products->data ?? null;
            $existingStripeProduct = null;
            if ($productsData && \is_array($productsData) && \count($productsData)) {
                $existingStripeProduct = \current($productsData);

                /**
                 * @TODO: Yeap, i know that metadata key is not unique on the Stripe side.
                 *        Probably we can add custom field for Magento product to map it with Stripe product.
                 */
                $this->existingStripeProducts[$sku] = $existingStripeProduct;
            }
        } catch (\Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
        }

        return $existingStripeProduct;
    }

    /**
     * Get prices by product id.
     *
     * @param string $stripeProductId
     * @return StripeCollection
     * @throws ApiErrorException
     */
    public function getPricesByProductId(string $stripeProductId)
    {
        $prices = $this->stripe->prices->all([
            'product' => $stripeProductId,
            'active' => true,
        ]);

        return $prices;
    }

    /**
     * Get price id by product sku.
     *
     * @param string $sku
     * @param string $priceType
     * @return string|null
     */
    public function getPriceIdByProductSku(string $sku, string $priceType): ?string
    {
        try {
            $existingStripeProduct = $this->getProductBySkuMetadata($sku);
            if (!$existingStripeProduct) {
                return null;
            }

            $existingStripeProductId = $existingStripeProduct->id ?? null;
            if (!$existingStripeProductId) {
                return null;
            }
            $prices = $this->getPricesByProductId((string)$existingStripeProductId);

            if (!\is_iterable($prices)) {
                return null;
            }

            foreach ($prices as $price) {
                if ($price->type !== $priceType) {
                    continue;
                }
                $existingStripePriceId = (string)($price->id);

                return $existingStripePriceId;
            }
        } catch (\Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
        }

        return null;
    }

    /**
     * Create product in Stripe.
     *
     * @param string $name
     * @param int|float $price
     * @param string $sku
     * @return StripeProduct
     * @throws ApiErrorException
     */
    public function createProduct(string $name, int|float $price, string $sku): StripeProduct
    {
        $product = $this->stripe->products->create([
            'name' => $name,
            /**
             * @TODO: Should be fixed somehow. Really don't have any idea.
             */
            'description' => '$' . $price . ' / Month subscription',
            'metadata' => [
                'sku' => $sku
            ]
        ]);

        return $product;
    }

    /**
     * Create price in Stripe.
     *
     * @param string $stripeProductId
     * @param int|float $price
     * @param string $type
     * @return StripePrice
     * @throws ApiErrorException
     */
    public function createPrice(string $stripeProductId, int|float $price, string $type): StripePrice
    {
        $options = [
            /**
             * Value should be in cents.
             */
            'unit_amount' => $price * 100,
            'product' => $stripeProductId,

            /**
             * @TODO: Should be fixed somehow. Really don't have any idea.
             */
            'currency' => 'usd',
        ];

        if ($type === 'recurring') {
            $options['recurring'] = ['interval' => 'month'];
        }

        $stripePrice = $this->stripe->prices->create($options);

        return $stripePrice;
    }

    /**
     * Create product in Stripe, create price for it and get price id.
     *
     * @param string $name
     * @param int|float $price
     * @param string $sku
     * @param string $type
     * @return string|null
     * @throws \Exception
     */
    public function getProductPriceId(string $name, int|float $price, string $sku, string $type): ?string
    {
        $type = match($type) {
            'recurring',
            'one_time' => $type,
            default => throw new \Exception('Unknown type for product type')
        };

        $existingStripePriceId = null;

        try {
            $productStripe = $this->getProductBySkuMetadata($sku);
            if ($productStripe === null) {
                $productStripe = $this->createProduct($name, $price, $sku);
            }

            $existingStripeProductId = $productStripe->id ?? null;
            $stripePrice = $this->createPrice((string)$existingStripeProductId, (float)$price, $type);

            $existingStripePriceId = ($stripePrice->id) ? (string)($stripePrice->id) : null;
        } catch (\Throwable $throwable) {
            $this->logger->error($this->logMessagePreparerInterface->getErrorMessage($throwable));
        }

        return $existingStripePriceId;
    }
}
