<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use function Setono\SyliusPlausiblePlugin\formatMoney;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class PurchaseSubscriber extends AbstractEventSubscriber
{
    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
        parent::__construct($eventDispatcher);
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

            $this->eventDispatcher->dispatch(
                (new Event(Events::PURCHASE))
                    ->setRevenue((string) $order->getCurrencyCode(), formatMoney($order->getTotal())),
            );
        } catch (\Throwable $e) {
            $this->log(Events::PURCHASE, $e);
        }
    }
}
