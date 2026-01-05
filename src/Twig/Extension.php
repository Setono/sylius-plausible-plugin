<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class Extension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            /** @phpstan-ignore argument.type (Twig runtime callable syntax) */
            new TwigFunction('setono_plausible_should_show_notification', [Runtime::class, 'shouldShowNotification']),
        ];
    }
}
