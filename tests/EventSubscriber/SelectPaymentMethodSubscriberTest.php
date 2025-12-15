<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Setono\SyliusPlausiblePlugin\EventSubscriber\SelectPaymentMethodSubscriber;

/**
 * @covers \Setono\SyliusPlausiblePlugin\EventSubscriber\SelectPaymentMethodSubscriber
 */
final class SelectPaymentMethodSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_subscribes_to_post_payment_event(): void
    {
        self::assertArrayHasKey('sylius.order.post_payment', SelectPaymentMethodSubscriber::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function it_dispatches_select_payment_method_event(): void
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(\Prophecy\Argument::that(fn ($event) => $event instanceof Event && $event->getName() === Events::SELECT_PAYMENT_METHOD))->shouldBeCalled();

        $subscriber = new SelectPaymentMethodSubscriber($eventDispatcher->reveal());
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
        $logger->error(\Prophecy\Argument::containingString('Select Payment Method'))->shouldBeCalled();

        $subscriber = new SelectPaymentMethodSubscriber($eventDispatcher->reveal());
        $subscriber->setLogger($logger->reveal());
        $subscriber->track();
    }
}
