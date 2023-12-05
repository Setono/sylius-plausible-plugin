<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPlausiblePlugin\Event\Plausible;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;

class EventTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_an_event(): void
    {
        $event = new Event('name');
        self::assertSame('name', $event->getName());
    }

    /**
     * @test
     *
     * @dataProvider getEvents
     */
    public function it_serializes(Event $event, string $expectedJson): void
    {
        self::assertSame($expectedJson, json_encode($event, \JSON_THROW_ON_ERROR | \JSON_FORCE_OBJECT));
    }

    /**
     * @test
     */
    public function it_serializes_server_side(): void
    {
        $event = (new Event('server_side'))->serverSide();
        self::assertSame('{"name":"server_side"}', json_encode($event, \JSON_THROW_ON_ERROR | \JSON_FORCE_OBJECT));
    }

    /**
     * @return \Generator<array-key, array{Event, string}>
     */
    public function getEvents(): \Generator
    {
        yield [
            new Event('name'),
            '{}',
        ];

        yield [
            (new Event('name'))->setProperty('foo', 'bar'),
            '{"props":{"foo":"bar"}}',
        ];

        yield [
            (new Event('name'))->setProperty('foo', 'bar')->setProperty('bar', 'baz'),
            '{"props":{"foo":"bar","bar":"baz"}}',
        ];

        yield [
            (new Event('name'))->setProperty('foo', 'bar')->setRevenue('USD', 100.45),
            '{"revenue":{"currency":"USD","amount":100.45},"props":{"foo":"bar"}}',
        ];
    }
}
