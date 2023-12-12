<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

final class AddressSubscriber extends AbstractEventSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.order.post_address' => 'track',
        ];
    }

    public function track(ResourceControllerEvent $resourceControllerEvent): void
    {
        try {
            /** @var OrderInterface|mixed $order */
            $order = $resourceControllerEvent->getSubject();
            Assert::isInstanceOf($order, OrderInterface::class);

            $this->eventBus->dispatch((new Event(Events::ADDRESS))->addContext('order', $order));
        } catch (\Throwable $e) {
            $this->log(Events::ADDRESS, $e);
        }
    }
}
