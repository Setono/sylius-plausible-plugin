<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Unit\Event\Plausible;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Revenue;

/**
 * @covers \Setono\SyliusPlausiblePlugin\Event\Plausible\Revenue
 */
final class RevenueTest extends TestCase
{
    /**
     * @test
     */
    public function it_holds_a_currency_and_an_amount(): void
    {
        $revenue = new Revenue('DKK', 99.95);

        self::assertSame('DKK', $revenue->currency);
        self::assertSame(99.95, $revenue->amount);
    }
}
