<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;

final class SelectPaymentMethodSubscriber extends AbstractEventSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.order.post_payment' => 'track',
        ];
    }

    public function track(): void
    {
        try {
            $this->eventDispatcher->dispatch(new Event(Events::SELECT_PAYMENT_METHOD));
        } catch (\Throwable $e) {
            $this->log(Events::SELECT_PAYMENT_METHOD, $e);
        }
    }
}
