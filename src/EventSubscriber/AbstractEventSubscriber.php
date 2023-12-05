<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class AbstractEventSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    protected LoggerInterface $logger;

    public function __construct(protected readonly MessageBusInterface $eventBus)
    {
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    protected function log(string $event, \Throwable $e): void
    {
        $this->logger->error(sprintf(
            'An error occurred trying to track the event %s: %s',
            $event,
            $e->getMessage(),
        ));
    }

    protected static function formatAmount(int $amount): float
    {
        return round($amount / 100, 2);
    }
}
