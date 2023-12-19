<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Message\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\SyliusPlausiblePlugin\Event\AlterEvent;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Message\Stamp\AlteredStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class AlterMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof Event && $envelope->last(AlteredStamp::class) === null) {
            $this->eventDispatcher->dispatch(new AlterEvent($message));
            $envelope = $envelope->with(new AlteredStamp());
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
