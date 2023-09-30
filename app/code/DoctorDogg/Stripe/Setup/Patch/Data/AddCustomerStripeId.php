<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use \Magento\Framework\Exception\AlreadyExistsException;
use \Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * Add customer Stripe id for relation with Stripe customer.
 */
class AddCustomerStripeId implements DataPatchInterface
{
    /**
     * @const string CUSTOMER_STRIPE_ID_KEY
     */
    private const CUSTOMER_STRIPE_ID_KEY = 'doctordogg_customer_stripe_id';

    /**
     * Add attribute to set.
     *
     * @param string $attributeCode
     * @param EavSetup $eavSetup
     * @return void
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws \Throwable
     */
    private function addAttributeToSet(string $attributeCode, EavSetup $eavSetup)
    {
        try {
            $eavSetup->addAttributeToSet(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                null,
                $attributeCode
            );

            $attribute = $this->eavConfig->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $attributeCode);

            $attribute->setData('used_in_forms', [
                'adminhtml_customer',
                'customer_account_create',
                'customer_account_edit'
            ]);
            $attribute->getResource()->save($attribute);
        } catch (\Throwable $throwable) {
            $this->logger->alert(
                'File: ' . $throwable->getFile() . ' '
                . 'on the line: ' . $throwable->getLine() . ' ' .
                $throwable->getMessage()
            );
            throw $throwable;
        }
    }

    /**
     * Add `doctordogg_customer_stripe_id` attribute to the customer.
     *
     * @param EavSetup $eavSetup
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Validator\ValidateException
     * @throws \Throwable
     */
    private function addCustomerStripeIdAttribute(EavSetup $eavSetup)
    {
        try {
            $eavSetup->removeAttribute(Customer::ENTITY, self::CUSTOMER_STRIPE_ID_KEY);
            $eavSetup->addAttribute(
                Customer::ENTITY,
                self::CUSTOMER_STRIPE_ID_KEY,
                [
                    'label' => 'Customer Stripe id',
                    'type' => 'varchar',
                    'input' => 'text',
                    'source' => Table::class,
                    'required' => false,
                    //'visible' => true,
                    'visible' => false,
                    'user_defined' => true,
                    'system' => false,
                    'position' => 0,
                    'admin_checkout' => 1,
                    'validate_rules' => '[]'
                ]
            );
            $this->addAttributeToSet(self::CUSTOMER_STRIPE_ID_KEY, $eavSetup);
        } catch (\Throwable $throwable) {
            $this->logger->alert(
                'File: ' . $throwable->getFile() . ' '
                . 'on the line: ' . $throwable->getLine() . ' ' .
                $throwable->getMessage()
            );
            throw $throwable;
        }
    }

    /**
     * Constructor.
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SchemaSetupInterface $schemaSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param EavConfig $eavConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        private CustomerSetupFactory $customerSetupFactory,
        private ModuleDataSetupInterface $moduleDataSetup,
        private SchemaSetupInterface $schemaSetup,
        private EavSetupFactory $eavSetupFactory,
        private EavConfig $eavConfig,
        private LoggerInterface $logger
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $this->addCustomerStripeIdAttribute($eavSetup);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
