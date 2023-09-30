<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model;

use \Magento\Framework\Exception\LocalizedException;
use \Magento\Quote\Model\Quote as MagentoQuote;
use \DoctorDogg\Stripe\Model\ResourceModel\Quote as QuoteResourceModel;
use \DoctorDogg\Stripe\Api\Data\CartInterface;

/**
 * Our model quote.
 * We need to use it as a provider of quote data by custom field.
 */
class Quote extends MagentoQuote implements CartInterface
{
    /**
     * Init resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(QuoteResourceModel::class);
    }

    /**
     * Loading by Stripe invoice ID.
     *
     * @param string $stripeInvoiceId
     * @return $this
     * @throws LocalizedException
     */
    public function loadByStripeInvoiceId(string $stripeInvoiceId): self
    {
        $this->_getResource()->loadByStripeInvoiceId($this, $stripeInvoiceId);
        return $this;
    }

    /**
     * Loading by Stripe invoice ID.
     *
     * @param string $stripeCheckoutSessionId
     * @return $this
     * @throws LocalizedException
     */
    public function loadByStripeCheckoutSessionId(string $stripeCheckoutSessionId): self
    {
        $this->_getResource()->loadByStripeCheckoutSessionId($this, $stripeCheckoutSessionId);
        return $this;
    }
}
