<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Unit\Event\Plausible;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Properties;
use Webmozart\Assert\InvalidArgumentException;

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

    /**
     * @test
     */
    public function it_is_empty_until_a_property_is_set(): void
    {
        $properties = new Properties();
        self::assertTrue($properties->isEmpty());

        $properties->set('property1', null);
        self::assertFalse($properties->isEmpty());
    }

    /**
     * @test
     */
    public function it_throws_when_getting_a_property_that_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The property with key "property1" does not exist');

        (new Properties())->get('property1');
    }

    /**
     * @test
     */
    public function it_throws_when_unsetting_a_property_that_does_not_exist(): void
    {
        $properties = new Properties();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The property with key "property1" does not exist');

        unset($properties['property1']);
    }

    /**
     * @test
     *
     * @dataProvider invalidOffsets
     */
    public function it_only_accepts_non_empty_string_offsets(?string $offset): void
    {
        $properties = new Properties();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Properties are key value pairs, where the key must be a string');

        $properties[$offset] = 'value';
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function invalidOffsets(): iterable
    {
        yield 'empty string' => [''];
        yield 'appending without a key' => [null];
    }

    /**
     * A property stored under an empty key through set() must still be unreachable through array access.
     *
     * @test
     *
     * @dataProvider arrayAccessOperations
     *
     * @param \Closure(Properties): mixed $operation
     */
    public function it_rejects_an_empty_offset_on_every_array_access(\Closure $operation): void
    {
        $properties = (new Properties())->set('', 'value');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Properties are key value pairs, where the key must be a string');

        $operation($properties);
    }

    /**
     * @return iterable<string, array{\Closure(Properties): mixed}>
     */
    public static function arrayAccessOperations(): iterable
    {
        yield 'isset' => [static fn (Properties $properties): bool => isset($properties[''])];
        yield 'read' => [static fn (Properties $properties): mixed => $properties['']];
    }

    /**
     * @test
     */
    public function it_tells_whether_a_property_exists(): void
    {
        $properties = new Properties();
        self::assertFalse($properties->has('property1'));

        $properties->set('property1', null);
        self::assertTrue($properties->has('property1'));
    }
}
