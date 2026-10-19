<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPlausiblePlugin\SetonoSyliusPlausiblePlugin;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;

/**
 * @covers \Setono\SyliusPlausiblePlugin\SetonoSyliusPlausiblePlugin
 */
final class SetonoSyliusPlausiblePluginTest extends TestCase
{
    /**
     * @test
     */
    public function it_only_supports_the_doctrine_orm_driver(): void
    {
        self::assertSame(
            [SyliusResourceBundle::DRIVER_DOCTRINE_ORM],
            (new SetonoSyliusPlausiblePlugin())->getSupportedDrivers(),
        );
    }
}
