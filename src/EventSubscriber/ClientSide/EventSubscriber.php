<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber\ClientSide;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Serializer\EventSerializerInterface;
use Setono\TagBag\Tag\InlineScriptTag;
use Setono\TagBag\TagBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EventSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly EventSerializerInterface $serializer,
        private readonly TagBagInterface $tagBag,
    ) {
        $this->logger = new NullLogger();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Event::class => ['add', -100],
        ];
    }

    public function add(Event $event): void
    {
        try {
            $json = $this->serializer->serialize($event, EventSerializerInterface::CONTEXT_CLIENT_SIDE);
        } catch (\Throwable $e) {
            $this->logger->error('Could not encode event to json', [
                'event' => $event,
                'exception' => $e,
            ]);

            return;
        }

        $this->tagBag->add(InlineScriptTag::create('{}' === $json ? sprintf('plausible("%s");', $event->getName()) : sprintf('plausible("%s", %s);', $event->getName(), $json)));
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
