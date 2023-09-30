<?php

declare(strict_types=1);

/**
 * The extension which provides the class with method.
 */
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'DoctorDogg_Stripe',
    __DIR__
);
