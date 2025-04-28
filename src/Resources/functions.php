<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin;

/** @psalm-suppress UndefinedClass,MixedArgument */
if (!\function_exists(formatMoney::class)) {
    function formatMoney(int $amount): float
    {
        return round($amount / 100, 2);
    }
}
