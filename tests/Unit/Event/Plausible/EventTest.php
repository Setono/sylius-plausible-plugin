<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Unit\Event\Plausible;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Properties;
use Sylius\Component\Core\Model\Order;

/**
 * @covers \Setono\SyliusPlausiblePlugin\Event\Plausible\Event
 */
final class EventTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_name(): void
    {
        self::assertSame('Purchase', (new Event('Purchase'))->getName());
    }

    /**
     * @test
     */
    public function it_starts_without_properties_revenue_or_order(): void
    {
        $event = new Event('Purchase');

        self::assertTrue($event->getProperties()->isEmpty());
        self::assertNull($event->getRevenue());
        self::assertNull($event->getOrder());
    }

    /**
     * @test
     */
    public function it_sets_and_gets_a_property(): void
    {
        $event = new Event('Purchase');
        self::assertFalse($event->hasProperty('order_id'));

        self::assertSame($event, $event->setProperty('order_id', 123));

        self::assertTrue($event->hasProperty('order_id'));
        self::assertSame(123, $event->getProperty('order_id'));
        self::assertSame(123, $event->getProperties()->get('order_id'));
    }

    /**
     * @test
     */
    public function it_replaces_the_properties(): void
    {
        $properties = (new Properties())->set('order_id', 123);
        $event = (new Event('Purchase'))->setProperty('coupon_code', 'SUMMER');

        self::assertSame($event, $event->setProperties($properties));

        self::assertSame($properties, $event->getProperties());
        self::assertFalse($event->hasProperty('coupon_code'));
    }

    /**
     * @test
     */
    public function it_sets_revenue(): void
    {
        $event = new Event('Purchase');

        self::assertSame($event, $event->setRevenue('EUR', 12.34));

        $revenue = $event->getRevenue();
        self::assertNotNull($revenue);
        self::assertSame('EUR', $revenue->currency);
        self::assertSame(12.34, $revenue->amount);
    }

    /**
     * @test
     */
    public function it_sets_and_unsets_the_order(): void
    {
        $order = new Order();
        $event = new Event('Purchase');

        self::assertSame($event, $event->setOrder($order));
        self::assertSame($order, $event->getOrder());

        $event->setOrder(null);
        self::assertNull($event->getOrder());
    }
}
