<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Processor;

use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Controller\ResultInterface;

/**
 * Interface which provides the possibility to get the payment link to the Stripe form.
 */
interface GetCheckoutSessionPaymentUrlInterface
{
    /**
     * Process the request.
     *
     * @param RequestInterface $request
     * @return ResultInterface
     */
    public function process(RequestInterface $request): ResultInterface;
}
