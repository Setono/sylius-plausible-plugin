<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Setono\SyliusPlausiblePlugin\EventSubscriber\PopulateOrderRelatedPropertiesSubscriber;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;

/**
 * @covers \Setono\SyliusPlausiblePlugin\EventSubscriber\PopulateOrderRelatedPropertiesSubscriber
 */
final class PopulateOrderRelatedPropertiesSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_subscribes_to_plausible_event(): void
    {
        self::assertArrayHasKey(Event::class, PopulateOrderRelatedPropertiesSubscriber::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function it_populates_event_with_order_properties(): void
    {
        $shippingMethod = $this->prophesize(ShippingMethodInterface::class);
        $shippingMethod->getCode()->willReturn('ups_ground');

        $shipment = $this->prophesize(ShipmentInterface::class);
        $shipment->getMethod()->willReturn($shippingMethod->reveal());

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getCode()->willReturn('stripe');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $coupon = $this->prophesize(PromotionCouponInterface::class);
        $coupon->getCode()->willReturn('DISCOUNT10');

        $order = $this->prophesize(OrderInterface::class);
        $order->getId()->willReturn(123);
        $order->getNumber()->willReturn('000000123');
        $order->getTotal()->willReturn(10000);
        $order->getTaxTotal()->willReturn(1000);
        $order->getShippingTotal()->willReturn(500);
        $order->getOrderPromotionTotal()->willReturn(-200);
        $order->getLastPayment()->willReturn($payment->reveal());
        $order->getShipments()->willReturn(new ArrayCollection([$shipment->reveal()]));
        $order->getPromotionCoupon()->willReturn($coupon->reveal());

        $cartContext = $this->prophesize(CartContextInterface::class);
        $cartContext->getCart()->willReturn($order->reveal());

        $event = new Event(Events::BEGIN_CHECKOUT);

        $subscriber = new PopulateOrderRelatedPropertiesSubscriber($cartContext->reveal());
        $subscriber->populate($event);

        self::assertSame(123, $event->getProperty('order_id'));
        self::assertSame('000000123', $event->getProperty('order_number'));
        self::assertSame(100.0, $event->getProperty('order_total'));
        self::assertSame(10.0, $event->getProperty('tax_total'));
        self::assertSame(5.0, $event->getProperty('shipping_total'));
        self::assertSame(-2.0, $event->getProperty('order_promotion_total'));
        self::assertSame('stripe', $event->getProperty('payment_method'));
        self::assertSame('ups_ground', $event->getProperty('shipping_method'));
        self::assertSame('DISCOUNT10', $event->getProperty('coupon_code'));
    }

    /**
     * @test
     */
    public function it_does_nothing_when_cart_not_found(): void
    {
        $cartContext = $this->prophesize(CartContextInterface::class);
        $cartContext->getCart()->willThrow(new CartNotFoundException());

        $event = new Event(Events::BEGIN_CHECKOUT);

        $subscriber = new PopulateOrderRelatedPropertiesSubscriber($cartContext->reveal());
        $subscriber->populate($event);

        self::assertFalse($event->hasProperty('order_id'));
    }

    /**
     * @test
     */
    public function it_does_nothing_when_order_has_no_id(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getId()->willReturn(null);

        $cartContext = $this->prophesize(CartContextInterface::class);
        $cartContext->getCart()->willReturn($order->reveal());

        $event = new Event(Events::BEGIN_CHECKOUT);

        $subscriber = new PopulateOrderRelatedPropertiesSubscriber($cartContext->reveal());
        $subscriber->populate($event);

        self::assertFalse($event->hasProperty('order_id'));
    }

    /**
     * @test
     */
    public function it_handles_missing_payment_and_shipping_methods(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getId()->willReturn(123);
        $order->getNumber()->willReturn('000000123');
        $order->getTotal()->willReturn(10000);
        $order->getTaxTotal()->willReturn(1000);
        $order->getShippingTotal()->willReturn(500);
        $order->getOrderPromotionTotal()->willReturn(0);
        $order->getLastPayment()->willReturn(null);
        $order->getShipments()->willReturn(new ArrayCollection());
        $order->getPromotionCoupon()->willReturn(null);

        $cartContext = $this->prophesize(CartContextInterface::class);
        $cartContext->getCart()->willReturn($order->reveal());

        $event = new Event(Events::BEGIN_CHECKOUT);

        $subscriber = new PopulateOrderRelatedPropertiesSubscriber($cartContext->reveal());
        $subscriber->populate($event);

        self::assertSame(123, $event->getProperty('order_id'));
        self::assertNull($event->getProperty('payment_method'));
        self::assertNull($event->getProperty('shipping_method'));
        self::assertNull($event->getProperty('coupon_code'));
    }
}
