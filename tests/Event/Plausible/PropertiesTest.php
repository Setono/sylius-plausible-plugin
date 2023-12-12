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
}
