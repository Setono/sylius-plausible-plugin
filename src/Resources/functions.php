<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin;

if (!\function_exists('Setono\SyliusPlausiblePlugin\formatMoney')) {
    function formatMoney(int $amount): float
    {
        return round($amount / 100, 2);
    }
}
