<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Controller\Stripe\Checkout\Invoice;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Action\HttpGetActionInterface;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\Exception\LocalizedException;
use \DoctorDogg\Stripe\Model\Processor\GetStripePaidResultInterface;

/**
 * Example of api requests to Stripe to make charge automatically.
 * @route: /doctordogg_stripe/stripe_checkout_invoice/pay/
 *
 * @ATTENTION!: Card should be attached manually to the account inside admin area of Stripe account
 * to have the possibility to try auto charge from card.
 */
class Pay extends Action implements HttpGetActionInterface
{
    /**
     * @var GetStripePaidResultInterface
     */
    private GetStripePaidResultInterface $getStripePaidResultInterface;

    /**
     * Constructor.
     *
     * @param GetStripePaidResultInterface $getStripePaidResultInterface
     * @param Context $context
     */
    public function __construct(
        GetStripePaidResultInterface $getStripePaidResultInterface,
        Context $context
    ) {
        $this->getStripePaidResultInterface = $getStripePaidResultInterface;
        parent::__construct(
            $context
        );
    }

    /**
     * @inheritdoc
     *
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute(): ResultInterface
    {
        return $this->getStripePaidResultInterface->pay($this->_request);
    }
}
