<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use function Setono\SyliusPlausiblePlugin\formatMoney;

final class FunctionsTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider amounts
     */
    public function it_formats_money_in_the_major_unit(int $amount, float $expected): void
    {
        self::assertSame($expected, formatMoney($amount));
    }

    /**
     * @return iterable<string, array{int, float}>
     */
    public static function amounts(): iterable
    {
        yield 'zero' => [0, 0.0];
        yield 'one minor unit' => [1, 0.01];
        yield 'whole major units' => [1200, 12.0];
        yield 'major and minor units' => [1234, 12.34];
        yield 'negative' => [-1234, -12.34];
    }

    /**
     * Composer autoloads the file, and the guard lets anything else include it again without
     * redeclaring the function.
     *
     * @test
     */
    public function it_can_be_included_more_than_once(): void
    {
        require __DIR__ . '/../../../src/Resources/functions.php';

        self::assertSame(12.34, formatMoney(1234));
    }
}
