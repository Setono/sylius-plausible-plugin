<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Setono\SyliusPlausiblePlugin\EventSubscriber\PurchaseSubscriber;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @covers \Setono\SyliusPlausiblePlugin\EventSubscriber\PurchaseSubscriber
 */
final class PurchaseSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_subscribes_to_kernel_request_event(): void
    {
        self::assertArrayHasKey(KernelEvents::REQUEST, PurchaseSubscriber::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function it_dispatches_purchase_event_with_revenue(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getCurrencyCode()->willReturn('USD');
        $order->getTotal()->willReturn(10000);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->find(123)->willReturn($order->reveal());

        $dispatchedEvent = null;
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Argument::type(Event::class))->will(
            function (array $args) use (&$dispatchedEvent): Event {
                /** @var Event $event */
                $event = $args[0];
                $dispatchedEvent = $event;

                return $event;
            },
        )->shouldBeCalled();

        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $session->set('sylius_order_id', 123);

        $request = new Request();
        $request->attributes->set('_route', 'sylius_shop_order_thank_you');
        $request->setSession($session);

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new PurchaseSubscriber($eventDispatcher->reveal(), $orderRepository->reveal());
        $subscriber->track($requestEvent);

        self::assertInstanceOf(Event::class, $dispatchedEvent);
        self::assertSame(Events::PURCHASE, $dispatchedEvent->getName());
        self::assertNotNull($dispatchedEvent->getRevenue());
        self::assertSame('USD', $dispatchedEvent->getRevenue()->currency);
        self::assertSame(100.0, $dispatchedEvent->getRevenue()->amount);
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_event_for_non_main_request(): void
    {
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $request = new Request();
        $request->attributes->set('_route', 'sylius_shop_order_thank_you');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::SUB_REQUEST);

        $subscriber = new PurchaseSubscriber($eventDispatcher->reveal(), $orderRepository->reveal());
        $subscriber->track($requestEvent);
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_event_for_other_routes(): void
    {
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $request = new Request();
        $request->attributes->set('_route', 'sylius_shop_homepage');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new PurchaseSubscriber($eventDispatcher->reveal(), $orderRepository->reveal());
        $subscriber->track($requestEvent);
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_event_when_order_id_is_not_in_session(): void
    {
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $session = new Session(new MockArraySessionStorage());
        $session->start();
        // Don't set sylius_order_id

        $request = new Request();
        $request->attributes->set('_route', 'sylius_shop_order_thank_you');
        $request->setSession($session);

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new PurchaseSubscriber($eventDispatcher->reveal(), $orderRepository->reveal());
        $subscriber->track($requestEvent);
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_event_when_order_not_found(): void
    {
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->find(123)->willReturn(null);

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $session->set('sylius_order_id', 123);

        $request = new Request();
        $request->attributes->set('_route', 'sylius_shop_order_thank_you');
        $request->setSession($session);

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new PurchaseSubscriber($eventDispatcher->reveal(), $orderRepository->reveal());
        $subscriber->track($requestEvent);
    }

    /**
     * @test
     */
    public function it_logs_error_when_exception_is_thrown(): void
    {
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->find(123)->willThrow(new \RuntimeException('Database error'));

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $logger = $this->prophesize(\Psr\Log\LoggerInterface::class);
        $logger->error(Argument::containingString('Purchase'))->shouldBeCalled();

        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $session->set('sylius_order_id', 123);

        $request = new Request();
        $request->attributes->set('_route', 'sylius_shop_order_thank_you');
        $request->setSession($session);

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new PurchaseSubscriber($eventDispatcher->reveal(), $orderRepository->reveal());
        $subscriber->setLogger($logger->reveal());
        $subscriber->track($requestEvent);
    }
}
