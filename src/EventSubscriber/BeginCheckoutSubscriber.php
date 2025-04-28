<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class BeginCheckoutSubscriber extends AbstractEventSubscriber
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

            $route = $requestEvent->getRequest()->attributes->get('_route');
            if ('sylius_shop_checkout_start' !== $route) {
                return;
            }

            $this->eventDispatcher->dispatch(new Event(Events::BEGIN_CHECKOUT));
        } catch (\Throwable $e) {
            $this->log(Events::BEGIN_CHECKOUT, $e);
        }
    }
}
