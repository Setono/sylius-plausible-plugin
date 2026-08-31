<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Properties;
use Setono\TagBag\Tag\InlineScriptTag;
use Setono\TagBag\TagBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class PlausibleEventSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    /**
     * These flags escape <, >, &, ' and " so that neither the event name nor a property value
     * can break out of the inline <script> element the payload is embedded in.
     */
    private const JSON_ESCAPE_FLAGS = \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT;

    private LoggerInterface $logger;

    public function __construct(
        private readonly SerializerInterface $serializer,
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
            $json = $this->serializer->serialize($event, 'json', [
                JsonEncode::OPTIONS => \JSON_FORCE_OBJECT | self::JSON_ESCAPE_FLAGS,
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                AbstractNormalizer::CALLBACKS => [
                    'properties' => static function (Properties $properties): ?Properties {
                        if ($properties->isEmpty()) {
                            return null;
                        }

                        return $properties;
                    },
                ],
            ]);

            $name = json_encode($event->getName(), \JSON_THROW_ON_ERROR | self::JSON_ESCAPE_FLAGS);
        } catch (\Throwable $e) {
            $this->logger->error('Could not encode event to json', [
                'event' => $event,
                'exception' => $e,
            ]);

            return;
        }

        $this->tagBag->add(InlineScriptTag::create(
            '{}' === $json ? sprintf('plausible(%s);', $name) : sprintf('plausible(%s, %s);', $name, $json),
        ));
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
