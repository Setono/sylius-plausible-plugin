<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

final class PurchaseSubscriber extends AbstractEventSubscriber
{
    public function __construct(
        MessageBusInterface $eventBus,
        private readonly OrderRepositoryInterface $orderRepository,
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
            if ('sylius_shop_order_thank_you' !== $route) {
                return;
            }

            $orderId = $request->getSession()->get('sylius_order_id');

            if (!is_scalar($orderId)) {
                return;
            }

            $order = $this->orderRepository->find($orderId);
            if (!$order instanceof OrderInterface) {
                return;
            }

            $this->eventBus->dispatch(
                (new Event(Events::PURCHASE))
                    ->addContext('order', $order)
                    ->setRevenue((string) $order->getCurrencyCode(), self::formatAmount($order->getTotal())),
            );
        } catch (\Throwable $e) {
            $this->log(Events::PURCHASE, $e);
        }
    }
}
