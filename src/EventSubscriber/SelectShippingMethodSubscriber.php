<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;

final class SelectShippingMethodSubscriber extends AbstractEventSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.order.post_select_shipping' => 'track',
        ];
    }

    public function track(): void
    {
        try {
            $this->eventBus->dispatch(new Event(Events::SELECT_SHIPPING_METHOD));
        } catch (\Throwable $e) {
            $this->log(Events::SELECT_SHIPPING_METHOD, $e);
        }
    }
}
