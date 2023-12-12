<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class PageviewSubscriber extends AbstractEventSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'track',
        ];
    }

    public function track(RequestEvent $requestEvent): void
    {
        try {
            if (!$requestEvent->isMainRequest()) {
                return;
            }

            $this->eventBus->dispatch(new Event(Events::PAGEVIEW));
        } catch (\Throwable $e) {
            $this->log(Events::PAGEVIEW, $e);
        }
    }
}
