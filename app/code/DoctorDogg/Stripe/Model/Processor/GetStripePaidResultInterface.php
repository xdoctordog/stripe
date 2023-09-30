<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Model\Processor;

use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\App\RequestInterface;

/**
 * Interface which provides the possibility to try to auto charge from the customer card.
 */
interface GetStripePaidResultInterface
{
    /**
     * Try to make auto payment from the client card.
     *
     * @param RequestInterface $request
     * @return ResultInterface
     */
    public function pay(RequestInterface $request): ResultInterface;
}
