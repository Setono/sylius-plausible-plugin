<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Message\EventHandler;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Setono\TagBag\Tag\InlineScriptTag;
use Setono\TagBag\TagBagInterface;

final class ClientSideEventHandler implements LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function __construct(private readonly TagBagInterface $tagBag)
    {
        $this->logger = new NullLogger();
    }

    public function __invoke(Event $event): void
    {
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

        $this->tagBag->add(
            InlineScriptTag::create(
                '{}' === $json ? sprintf('plausible("%s");', $event->getName()) : sprintf('plausible("%s", %s);', $event->getName(), $json),
            )->withPriority($event->getName() === Events::PAGEVIEW ? 5 : 0), // make sure the pageview event is sent before other events
        );
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
