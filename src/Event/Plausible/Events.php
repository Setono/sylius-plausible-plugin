<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Event\Plausible;

final class Events
{
    final public const BEGIN_CHECKOUT = 'Begin Checkout';

    final public const PURCHASE = 'Purchase';

    private function __construct()
    {
    }
}
