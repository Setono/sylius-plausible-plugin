<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Twig;

use Setono\SyliusPlausiblePlugin\Checker\NotificationCheckerInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class Runtime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly NotificationCheckerInterface $notificationChecker,
    ) {
    }

    public function shouldShowNotification(): bool
    {
        return $this->notificationChecker->shouldShowNotification();
    }
}
