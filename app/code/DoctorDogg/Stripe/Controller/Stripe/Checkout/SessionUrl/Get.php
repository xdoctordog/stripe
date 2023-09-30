<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Controller\Stripe\Checkout\SessionUrl;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Action\HttpGetActionInterface;
use \Magento\Framework\Controller\ResultInterface;
use \DoctorDogg\Stripe\Model\Processor\GetCheckoutSessionPaymentUrlInterface;

/**
 * Get checkout Stripe session url. To provide the customer with possibility to fill the form.
 * @route: /doctordogg_stripe/stripe_checkout_sessionurl/get/
 */
class Get extends Action implements HttpGetActionInterface
{
    /**
     * @var GetCheckoutSessionPaymentUrlInterface
     */
    private GetCheckoutSessionPaymentUrlInterface $getCheckoutSessionPaymentUrlInterface;

    /**
     * Constructor.
     *
     * @param GetCheckoutSessionPaymentUrlInterface $getCheckoutSessionPaymentUrlInterface
     * @param Context $context
     */
    public function __construct(
        GetCheckoutSessionPaymentUrlInterface $getCheckoutSessionPaymentUrlInterface,
        Context $context
    ) {
        $this->getCheckoutSessionPaymentUrlInterface = $getCheckoutSessionPaymentUrlInterface;
        parent::__construct(
            $context
        );
    }

    /**
     * @inheritdoc
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        return $this->getCheckoutSessionPaymentUrlInterface
            ->process($this->_request);
    }
}
