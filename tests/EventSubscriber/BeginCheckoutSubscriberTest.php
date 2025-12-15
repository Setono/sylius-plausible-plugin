<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Setono\SyliusPlausiblePlugin\EventSubscriber\BeginCheckoutSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @covers \Setono\SyliusPlausiblePlugin\EventSubscriber\BeginCheckoutSubscriber
 */
final class BeginCheckoutSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_subscribes_to_kernel_request_event(): void
    {
        self::assertArrayHasKey(KernelEvents::REQUEST, BeginCheckoutSubscriber::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function it_dispatches_begin_checkout_event_on_checkout_start_route(): void
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(\Prophecy\Argument::that(fn ($event) => $event instanceof Event && $event->getName() === Events::BEGIN_CHECKOUT))->shouldBeCalled();

        $request = new Request();
        $request->attributes->set('_route', 'sylius_shop_checkout_start');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new BeginCheckoutSubscriber($eventDispatcher->reveal());
        $subscriber->track($requestEvent);
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_event_for_non_main_request(): void
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(\Prophecy\Argument::any())->shouldNotBeCalled();

        $request = new Request();
        $request->attributes->set('_route', 'sylius_shop_checkout_start');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::SUB_REQUEST);

        $subscriber = new BeginCheckoutSubscriber($eventDispatcher->reveal());
        $subscriber->track($requestEvent);
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_event_for_other_routes(): void
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(\Prophecy\Argument::any())->shouldNotBeCalled();

        $request = new Request();
        $request->attributes->set('_route', 'sylius_shop_homepage');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new BeginCheckoutSubscriber($eventDispatcher->reveal());
        $subscriber->track($requestEvent);
    }

    /**
     * @test
     */
    public function it_logs_error_when_exception_is_thrown(): void
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(\Prophecy\Argument::any())->willThrow(new \RuntimeException('Test error'));

        $logger = $this->prophesize(\Psr\Log\LoggerInterface::class);
        $logger->error(\Prophecy\Argument::containingString('Begin Checkout'))->shouldBeCalled();

        $request = new Request();
        $request->attributes->set('_route', 'sylius_shop_checkout_start');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new BeginCheckoutSubscriber($eventDispatcher->reveal());
        $subscriber->setLogger($logger->reveal());
        $subscriber->track($requestEvent);
    }
}
