<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;

final class AddressSubscriber extends AbstractEventSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.order.post_address' => 'track',
        ];
    }

    public function track(): void
    {
        try {
            $this->eventBus->dispatch(new Event(Events::ADDRESS));
        } catch (\Throwable $e) {
            $this->log(Events::ADDRESS, $e);
        }
    }
}
