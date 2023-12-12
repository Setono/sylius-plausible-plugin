<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

final class AddShippingInfoSubscriber extends AbstractEventSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.order.post_select_shipping' => 'track',
        ];
    }

    public function track(ResourceControllerEvent $resourceControllerEvent): void
    {
        try {
            /** @var OrderInterface|mixed $order */
            $order = $resourceControllerEvent->getSubject();
            Assert::isInstanceOf($order, OrderInterface::class);

            $shippingMethodCode = null;
            foreach ($order->getShipments() as $shipment) {
                $shippingMethod = $shipment->getMethod();
                if (null === $shippingMethod) {
                    continue;
                }

                $shippingMethodCode = $shippingMethod->getCode();
            }
            Assert::notNull($shippingMethodCode);

            $this->eventBus->dispatch(
                (new Event(Events::ADD_SHIPPING_INFO))
                    ->setProperty('order_id', (string) $order->getId())
                    ->setProperty('order_number', (string) $order->getNumber())
                    ->setProperty('shipping_method', $shippingMethodCode),
            );
        } catch (\Throwable $e) {
            $this->log(Events::ADD_PAYMENT_INFO, $e);
        }
    }
}
