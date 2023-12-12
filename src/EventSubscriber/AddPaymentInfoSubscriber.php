<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

final class AddPaymentInfoSubscriber extends AbstractEventSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.order.post_payment' => 'track',
        ];
    }

    public function track(ResourceControllerEvent $resourceControllerEvent): void
    {
        try {
            /** @var OrderInterface|mixed $order */
            $order = $resourceControllerEvent->getSubject();
            Assert::isInstanceOf($order, OrderInterface::class);

            $lastPayment = $order->getLastPayment();
            Assert::notNull($lastPayment);

            $paymentMethod = $lastPayment->getMethod();
            Assert::notNull($paymentMethod);

            $paymentMethodCode = $paymentMethod->getCode();
            Assert::notNull($paymentMethodCode);

            $this->eventBus->dispatch(
                (new Event(Events::ADD_PAYMENT_INFO))
                    ->setProperty('order_id', (string) $order->getId())
                    ->setProperty('order_number', (string) $order->getNumber())
                    ->setProperty('payment_method', $paymentMethodCode),
            );
        } catch (\Throwable $e) {
            $this->log(Events::ADD_PAYMENT_INFO, $e);
        }
    }
}
