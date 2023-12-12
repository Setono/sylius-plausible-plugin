<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

final class BeginCheckoutSubscriber extends AbstractEventSubscriber
{
    public function __construct(
        MessageBusInterface $eventBus,
        private readonly CartContextInterface $cartContext,
    ) {
        parent::__construct($eventBus);
    }

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
            $request = $requestEvent->getRequest();

            $route = $request->attributes->get('_route');
            if ('sylius_shop_checkout_start' !== $route) {
                return;
            }

            /** @var OrderInterface $order */
            $order = $this->cartContext->getCart();
            Assert::isInstanceOf($order, OrderInterface::class);

            $this->eventBus->dispatch(new Event(Events::BEGIN_CHECKOUT));
        } catch (\Throwable $e) {
            $this->log(Events::BEGIN_CHECKOUT, $e);
        }
    }
}
