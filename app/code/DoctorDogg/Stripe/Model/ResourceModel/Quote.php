<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\ResourceModel;

use \Magento\Quote\Model\Quote as QuoteMagento;
use \Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModelMagento;
use \DoctorDogg\Stripe\Api\Data\CartInterface;

/**
 * Our custom quote resource model.
 * We need to get the quotes from the db by our custom field `doctordogg_stripe_invoice_id`
 */
class Quote extends QuoteResourceModelMagento
{
    /**
     * Load quote by Stripe invoice id.
     *
     * @param QuoteMagento $quote
     * @param string $stripeInvoiceId
     * @return $this
     */
    public function loadByStripeInvoiceId(QuoteMagento $quote, string $stripeInvoiceId)
    {
        return $this->loadByField($quote, $stripeInvoiceId, CartInterface::STRIPE_INVOICE_ID);
    }

    /**
     * Load quote by Stripe checkout session id.
     *
     * @param QuoteMagento $quote
     * @param string $stripeInvoiceId
     * @return $this
     */
    public function loadByStripeCheckoutSessionId(QuoteMagento $quote, string $stripeCheckoutSessionId)
    {
        return $this->loadByField($quote, $stripeCheckoutSessionId, CartInterface::STRIPE_CHECKOUT_SESSION_ID);
    }

    /**
     * Load by field value from the db table.
     *
     * @param $quote
     * @param string $value
     * @param string $field
     * @return $this
     */
    public function loadByField($quote, string $value, string $field)
    {
        $connection = $this->getConnection();
        $select = $this->_getLoadSelect($field, $value, $quote)
            //->where('is_active = ?', 1)
        ;

        $data = $connection->fetchRow($select);
        if ($data) {
            $quote->setData($data);
            $quote->setOrigData();
        }

        $this->_afterLoad($quote);

        return $this;
    }
}
