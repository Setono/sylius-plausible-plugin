<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Setono\SyliusPlausiblePlugin\EventSubscriber\AddressSubscriber;

/**
 * @covers \Setono\SyliusPlausiblePlugin\EventSubscriber\AddressSubscriber
 */
final class AddressSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_subscribes_to_post_address_event(): void
    {
        self::assertArrayHasKey('sylius.order.post_address', AddressSubscriber::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function it_dispatches_address_event(): void
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(\Prophecy\Argument::that(fn ($event) => $event instanceof Event && $event->getName() === Events::ADDRESS))->shouldBeCalled();

        $subscriber = new AddressSubscriber($eventDispatcher->reveal());
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
        $logger->error(\Prophecy\Argument::containingString('Address'))->shouldBeCalled();

        $subscriber = new AddressSubscriber($eventDispatcher->reveal());
        $subscriber->setLogger($logger->reveal());
        $subscriber->track();
    }
}
