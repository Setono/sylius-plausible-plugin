<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin;

if (!\function_exists('Setono\SyliusPlausiblePlugin\formatMoney')) {
    /**
     * Converts a Sylius money amount, which is an integer in the currency's minor unit, into the
     * major unit Plausible expects for revenue.
     *
     * This assumes two minor unit digits for every currency, which matches how Sylius itself
     * treats money throughout the core. Zero decimal currencies such as JPY, and three decimal
     * currencies such as KWD, are therefore reported off by a factor of 100 and 10 respectively.
     */
    function formatMoney(int $amount): float
    {
        return round($amount / 100, 2);
    }
}
