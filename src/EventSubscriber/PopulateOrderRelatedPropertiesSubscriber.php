<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\AlterEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Sylius\Component\Shipping\Model\ShipmentInterface;
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
            ->setProperty('shipping_method', self::getLastShipment($order)?->getMethod()?->getCode())
            ->setProperty('coupon_code', $order->getPromotionCoupon()?->getCode())
        ;
    }

    private static function getLastShipment(OrderInterface $order): ?ShipmentInterface
    {
        $lastShipment = $order->getShipments()->last();
        if (!$lastShipment instanceof ShipmentInterface) {
            return null;
        }

        return $lastShipment;
    }
}
