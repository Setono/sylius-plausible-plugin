<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\PreSendEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

final class PopulateOrderRelatedPropertiesSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PreSendEvent::class => 'populate',
        ];
    }

    public function populate(PreSendEvent $event): void
    {
        $context = $event->event->getContext();
        if (!isset($context['order'])) {
            return;
        }

        /** @var OrderInterface|mixed $order */
        $order = $context['order'];
        Assert::isInstanceOf($order, OrderInterface::class);

        $event->event
            ->setProperty('order_id', (string) $order->getId())
            ->setProperty('order_number', (string) $order->getNumber())
        ;

        self::populateShippingMethod($order, $event->event);
        self::populatePaymentMethod($order, $event->event);
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

        if (null !== $shippingMethodCode) {
            $event->setProperty('shipping_method', $shippingMethodCode);
        }
    }

    private static function populatePaymentMethod(OrderInterface $order, Event $event): void
    {
        $paymentMethodCode = $order->getLastPayment()?->getMethod()?->getCode();
        if (null !== $paymentMethodCode) {
            $event->setProperty('payment_method', $paymentMethodCode);
        }
    }
}
