<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Event\Plausible;

final class Revenue
{
    public function __construct(public string $currency, public float $amount)
    {
    }
}
