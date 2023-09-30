<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Setup\Patch\Schema;

use \Magento\Framework\Setup\ModuleDataSetupInterface;
use \Magento\Framework\Setup\Patch\SchemaPatchInterface;
use \Magento\Quote\Setup\QuoteSetup;
use \Magento\Quote\Setup\QuoteSetupFactory;
use \Magento\Sales\Setup\SalesSetup;
use \Magento\Sales\Setup\SalesSetupFactory;
use \DoctorDogg\Stripe\Api\Data\CartInterface;

/**
 * Add new field `doctordogg_stripe_checkout_session_id` to quote and order table.
 */
class AddStripeCheckoutSessionIdToQuoteAndOrder implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var QuoteSetupFactory
     */
    private QuoteSetupFactory $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private SalesSetupFactory $salesSetupFactory;

    /**
     * Constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * Do Upgrade.
     *
     * @return void
     */
    public function apply()
    {
        /**
         * @var QuoteSetup $quoteSetup
         */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $this->moduleDataSetup]);

        /**
         * @var SalesSetup $salesSetup
         */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributeOptions = [
            'type' => 'varchar',
            'visible' => true,
            'required' => false
        ];
        $quoteSetup->addAttribute('quote', CartInterface::STRIPE_CHECKOUT_SESSION_ID, $attributeOptions);
        $salesSetup->addAttribute('order', CartInterface::STRIPE_CHECKOUT_SESSION_ID, $attributeOptions);
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
