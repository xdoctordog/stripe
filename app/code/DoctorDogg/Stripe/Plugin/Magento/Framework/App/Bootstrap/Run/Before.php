<?php

declare(strict_types=1);

namespace DoctorDogg\Stripe\Plugin\Magento\Framework\App\Bootstrap\Run;

use \Magento\Framework\App\Bootstrap;
use \Magento\Framework\App\Http as HttpApp;

/**
 * Plugin before for handling the webhooks.
 */
class Before
{

    public function beforeRun(
        Bootstrap $subject,
        HttpApp $app
    )
    {
        throw new \Exception('WE ARE HERE!');

        return $app;
    }
}
