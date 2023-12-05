<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Message\EventHandler;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\PreSendEvent;
use Setono\TagBag\Tag\InlineScriptTag;
use Setono\TagBag\TagBagInterface;

final class ClientSideEventHandler implements LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly TagBagInterface $tagBag,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $this->logger = new NullLogger();
    }

    public function __invoke(Event $event): void
    {
        $preSendEvent = new PreSendEvent($event);
        $this->eventDispatcher->dispatch($preSendEvent);

        if (!$preSendEvent->send) {
            return;
        }

        $event->clientSide();

        try {
            $json = json_encode($event, \JSON_THROW_ON_ERROR | \JSON_FORCE_OBJECT);
        } catch (\JsonException $e) {
            $this->logger->error('Could not encode event to json', [
                'event' => $event,
                'exception' => $e,
            ]);

            return;
        }

        $this->tagBag->add(InlineScriptTag::create(
            sprintf('plausible("%s", %s);', $event->getName(), $json),
        ));
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
