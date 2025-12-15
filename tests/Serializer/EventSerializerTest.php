<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Serializer;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use function Setono\SyliusPlausiblePlugin\formatMoney;
use Setono\SyliusPlausiblePlugin\Serializer\EventSerializer;
use Setono\SyliusPlausiblePlugin\Serializer\EventSerializerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;

/**
 * @covers \Setono\SyliusPlausiblePlugin\Serializer\EventSerializer
 */
final class EventSerializerTest extends KernelTestCase
{
    private EventSerializer $eventSerializer;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $serializer = $container->get('serializer');
        Assert::isInstanceOf($serializer, SerializerInterface::class);

        $this->eventSerializer = new EventSerializer($serializer);
    }

    /**
     * @test
     */
    public function it_serializes_event_with_properties_and_revenue(): void
    {
        $event = (new Event(Events::PURCHASE))
            ->setProperty('prop1', 'value1')
            ->setRevenue('USD', formatMoney(1234))
        ;

        self::assertSame(
            '{"props":{"prop1":"value1"},"revenue":{"currency":"USD","amount":12.34}}',
            $this->eventSerializer->serialize($event, EventSerializerInterface::CONTEXT_CLIENT_SIDE),
        );
    }

    /**
     * @test
     */
    public function it_serializes_event_with_revenue_only(): void
    {
        $event = (new Event(Events::PURCHASE))
            ->setRevenue('USD', formatMoney(1234))
        ;

        self::assertSame(
            '{"revenue":{"currency":"USD","amount":12.34}}',
            $this->eventSerializer->serialize($event, EventSerializerInterface::CONTEXT_CLIENT_SIDE),
        );
    }

    /**
     * @test
     */
    public function it_serializes_event_with_properties_only(): void
    {
        $event = (new Event(Events::BEGIN_CHECKOUT))
            ->setProperty('order_id', 123)
            ->setProperty('order_total', 99.99)
        ;

        self::assertSame(
            '{"props":{"order_id":123,"order_total":99.99}}',
            $this->eventSerializer->serialize($event, EventSerializerInterface::CONTEXT_CLIENT_SIDE),
        );
    }

    /**
     * @test
     */
    public function it_serializes_event_without_properties_or_revenue_as_empty_object(): void
    {
        $event = new Event(Events::ADDRESS);

        self::assertSame(
            '{}',
            $this->eventSerializer->serialize($event, EventSerializerInterface::CONTEXT_CLIENT_SIDE),
        );
    }

    /**
     * @test
     */
    public function it_excludes_null_property_values(): void
    {
        $event = (new Event(Events::PURCHASE))
            ->setProperty('order_id', 123)
            ->setProperty('coupon_code', null)
            ->setRevenue('EUR', 50.0)
        ;

        $json = $this->eventSerializer->serialize($event, EventSerializerInterface::CONTEXT_CLIENT_SIDE);

        self::assertStringContainsString('"order_id":123', $json);
        self::assertStringNotContainsString('coupon_code', $json);
    }

    /**
     * @test
     */
    public function it_excludes_empty_string_property_values(): void
    {
        $event = (new Event(Events::PURCHASE))
            ->setProperty('order_id', 123)
            ->setProperty('coupon_code', '')
            ->setRevenue('EUR', 50.0)
        ;

        $json = $this->eventSerializer->serialize($event, EventSerializerInterface::CONTEXT_CLIENT_SIDE);

        self::assertStringContainsString('"order_id":123', $json);
        self::assertStringNotContainsString('coupon_code', $json);
    }
}
