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

    public function track(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || $event->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $accept = $event->getRequest()->headers->get('Accept');
        if (!is_string($accept) || !str_contains($accept, 'html')) {
            return;
        }

        try {
            $this->eventBus->dispatch(new Event(Events::PAGEVIEW));
        } catch (\Throwable $e) {
            $this->log(Events::PAGEVIEW, $e);
        }
    }
}
