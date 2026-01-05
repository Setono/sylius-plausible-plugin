<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Checker;

interface NotificationCheckerInterface
{
    /**
     * Returns true if the notification bar should be shown to the current admin user.
     */
    public function shouldShowNotification(): bool;
}
