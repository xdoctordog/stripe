<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Stripe\Product;

use \Stripe\Collection as StripeCollection;
use \Stripe\Exception\ApiErrorException;
use \Stripe\Price as StripePrice;
use \Stripe\Product as StripeProduct;

/**
 * Interface for Stripe Product Manager.
 */
interface ManagementInterface
{
    /**
     * @TODO: Add methods.
     */

    /**
     * Get product by sku metadata.
     *
     * @param string $sku
     * @return StripeProduct|null
     */
    public function getProductBySkuMetadata(string $sku): ?StripeProduct;

    /**
     * Get prices by product id.
     *
     * @param string $stripeProductId
     * @return StripeCollection
     * @throws ApiErrorException
     */
    public function getPricesByProductId(string $stripeProductId);

    /**
     * Get price id by product sku.
     *
     * @param string $sku
     * @param string $priceType
     * @return string|null
     */
    public function getPriceIdByProductSku(string $sku, string $priceType): ?string;

    /**
     * Create product in Stripe.
     *
     * @param string $name
     * @param int|float $price
     * @param string $sku
     * @return StripeProduct
     * @throws ApiErrorException
     */
    public function createProduct(string $name, int|float $price, string $sku): StripeProduct;

    /**
     * Create price in Stripe.
     *
     * @param string $stripeProductId
     * @param int|float $price
     * @param string $type
     * @return StripePrice
     * @throws ApiErrorException
     */
    public function createPrice(string $stripeProductId, int|float $price, string $type): StripePrice;

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
    public function getProductPriceId(string $name, int|float $price, string $sku, string $type): ?string;
}
