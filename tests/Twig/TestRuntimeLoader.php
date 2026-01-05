<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Twig;

use Setono\SyliusPlausiblePlugin\Checker\NotificationCheckerInterface;
use Setono\SyliusPlausiblePlugin\Twig\Runtime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

final class TestRuntimeLoader implements RuntimeLoaderInterface
{
    /**
     * @param string $class
     *
     * @return object|null
     */
    public function load($class)
    {
        if (Runtime::class === $class) {
            $checker = new TestNotificationChecker();

            return new Runtime($checker);
        }

        return null;
    }
}

final class TestNotificationChecker implements NotificationCheckerInterface
{
    public function shouldShowNotification(): bool
    {
        return ExtensionTest::$shouldShowNotification;
    }
}
