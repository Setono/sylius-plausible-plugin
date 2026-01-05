<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Twig;

use Setono\SyliusPlausiblePlugin\Twig\Extension;
use Twig\Test\IntegrationTestCase;

final class ExtensionTest extends IntegrationTestCase
{
    public static bool $shouldShowNotification = false;

    public static function setShouldShowNotification(bool $value): void
    {
        self::$shouldShowNotification = $value;
    }

    protected function getExtensions(): array
    {
        return [
            new Extension(),
        ];
    }

    protected function getRuntimeLoaders(): array
    {
        return [
            new TestRuntimeLoader(),
        ];
    }

    protected function getFixturesDir(): string
    {
        return __DIR__ . '/Fixtures/';
    }
}
