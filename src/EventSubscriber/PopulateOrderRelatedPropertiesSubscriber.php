<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use function Setono\SyliusPlausiblePlugin\formatMoney;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
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
            Event::class => 'populate',
        ];
    }

    public function populate(Event $event): void
    {
        // The event knows its own order for events dispatched outside the cart flow, most
        // importantly the purchase event: on the thank you page the completed order is no
        // longer in the 'cart' state, so the cart context would return an unrelated cart.
        $order = $event->getOrder() ?? $this->getCart();

        if (null === $order) {
            return;
        }

        // if the order id is null it means that the order has not been persisted yet
        if ($order->getId() === null) {
            return;
        }

        $event
            ->setProperty('order_id', $order->getId())
            ->setProperty('order_number', $order->getNumber())
            ->setProperty('order_total', formatMoney($order->getTotal()))
            ->setProperty('tax_total', formatMoney($order->getTaxTotal()))
            ->setProperty('shipping_total', formatMoney($order->getShippingTotal()))
            ->setProperty('order_promotion_total', formatMoney($order->getOrderPromotionTotal()))
            ->setProperty('payment_method', $order->getLastPayment()?->getMethod()?->getCode())
            ->setProperty('shipping_method', self::getLastShipment($order)?->getMethod()?->getCode())
            ->setProperty('coupon_code', $order->getPromotionCoupon()?->getCode())
        ;
    }

    private function getCart(): ?OrderInterface
    {
        try {
            $cart = $this->cartContext->getCart();
        } catch (CartNotFoundException) {
            return null;
        }

        return $cart instanceof OrderInterface ? $cart : null;
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
