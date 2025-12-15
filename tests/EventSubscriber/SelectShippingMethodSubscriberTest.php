<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Setono\SyliusPlausiblePlugin\EventSubscriber\SelectShippingMethodSubscriber;

/**
 * @covers \Setono\SyliusPlausiblePlugin\EventSubscriber\SelectShippingMethodSubscriber
 */
final class SelectShippingMethodSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_subscribes_to_post_select_shipping_event(): void
    {
        self::assertArrayHasKey('sylius.order.post_select_shipping', SelectShippingMethodSubscriber::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function it_dispatches_select_shipping_method_event(): void
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(\Prophecy\Argument::that(function ($event) {
            return $event instanceof Event && $event->getName() === Events::SELECT_SHIPPING_METHOD;
        }))->shouldBeCalled();

        $subscriber = new SelectShippingMethodSubscriber($eventDispatcher->reveal());
        $subscriber->track();
    }

    /**
     * @test
     */
    public function it_logs_error_when_exception_is_thrown(): void
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(\Prophecy\Argument::any())->willThrow(new \RuntimeException('Test error'));

        $logger = $this->prophesize(\Psr\Log\LoggerInterface::class);
        $logger->error(\Prophecy\Argument::containingString('Select Shipping Method'))->shouldBeCalled();

        $subscriber = new SelectShippingMethodSubscriber($eventDispatcher->reveal());
        $subscriber->setLogger($logger->reveal());
        $subscriber->track();
    }
}
