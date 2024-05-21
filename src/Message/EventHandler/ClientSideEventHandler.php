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

        $tag = InlineScriptTag::create('{}' === $json ? sprintf('plausible("%s");', $event->getName()) : sprintf('plausible("%s", %s);', $event->getName(), $json));

        if ($event->getName() === Events::PAGEVIEW) {
            $tag = $tag
                // make sure the pageview event is sent before other events
                ->withPriority(5)
                // the default pageview event is triggered on the request event, but if you have data you want to add to
                // the pageview event in the controller you can trigger it there instead
                // and set the fingerprint to 'plausible-pageview' with a priority higher than 5
                ->withFingerprint('plausible-pageview')
            ;
        }

        $this->tagBag->add($tag);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
