<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPlausiblePlugin\Event\Plausible;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Properties;

/**
 * @covers \Setono\SyliusPlausiblePlugin\Event\Plausible\Properties
 */
final class PropertiesTest extends TestCase
{
    /**
     * @test
     */
    public function it_json_serializes(): void
    {
        $properties = new Properties();
        $properties->set('property1', 'value');
        $properties->set('property2', '');
        $properties->set('property3', null);

        self::assertSame([
            'property1' => 'value',
        ], $properties->jsonSerialize());
    }

    /**
     * @test
     */
    public function it_is_countable(): void
    {
        $properties = new Properties();
        self::assertCount(0, $properties);

        $properties->set('property1', 'value');
        self::assertCount(1, $properties);
    }

    /**
     * @test
     */
    public function it_is_iterable(): void
    {
        $properties = new Properties();
        self::assertSame([], iterator_to_array($properties));

        $properties->set('property1', 'value');
        self::assertSame(['property1' => 'value'], iterator_to_array($properties));
    }

    /**
     * @test
     */
    public function it_is_array_accessible(): void
    {
        $properties = new Properties();
        self::assertFalse(isset($properties['property1']));

        $properties['property1'] = 'value';
        self::assertTrue(isset($properties['property1']));
        self::assertSame('value', $properties['property1']);

        unset($properties['property1']);
        self::assertFalse(isset($properties['property1']));
    }
}
