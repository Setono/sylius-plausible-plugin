<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\AlterEvent;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PopulateOrderRelatedPropertiesSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CartContextInterface $cartContext)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AlterEvent::class => 'populate',
        ];
    }

    public function populate(AlterEvent $event): void
    {
        try {
            $order = $this->cartContext->getCart();
        } catch (CartNotFoundException) {
            return;
        }

        if (!$order instanceof OrderInterface) {
            return;
        }

        /** @var mixed $orderId */
        $orderId = $order->getId();

        // if the order id is null it means that the order has not been persisted yet
        if (null === $orderId) {
            return;
        }

        $event->event
            ->setProperty('order_id', $orderId)
            ->setProperty('order_number', $order->getNumber())
            ->setProperty('order_total', $order->getTotal())
            ->setProperty('tax_total', $order->getTaxTotal())
            ->setProperty('shipping_total', $order->getShippingTotal())
            ->setProperty('order_promotion_total', $order->getOrderPromotionTotal())
            ->setProperty('payment_method', $order->getLastPayment()?->getMethod()?->getCode())
            ->setProperty('coupon_code', $order->getPromotionCoupon()?->getCode())
        ;

        self::populateShippingMethod($order, $event->event);
    }

    private static function populateShippingMethod(OrderInterface $order, Event $event): void
    {
        $shippingMethodCode = null;
        foreach ($order->getShipments() as $shipment) {
            $shippingMethod = $shipment->getMethod();
            if (null === $shippingMethod) {
                continue;
            }

            $shippingMethodCode = $shippingMethod->getCode();
        }

        $event->setProperty('shipping_method', $shippingMethodCode);
    }
}
