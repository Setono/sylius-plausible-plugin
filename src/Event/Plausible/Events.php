<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Event\Plausible;

final class Events
{
    final public const ADDRESS = 'Address';

    final public const BEGIN_CHECKOUT = 'Begin Checkout';

    final public const PURCHASE = 'Purchase';

    final public const SELECT_SHIPPING_METHOD = 'Select Shipping Method';

    final public const SELECT_PAYMENT_METHOD = 'Select Payment Method';

    private function __construct()
    {
    }
}
