<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Serializer;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Properties;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class EventSerializer implements EventSerializerInterface
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    public function serialize(Event $event, string $context): string
    {
        return $this->serializer->serialize($event, 'json', [
            JsonEncode::OPTIONS => \JSON_FORCE_OBJECT,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractNormalizer::GROUPS => [$context],
            AbstractNormalizer::CALLBACKS => [
                'properties' => static function (Properties $properties): ?Properties {
                    if ($properties->isEmpty()) {
                        return null;
                    }

                    return $properties;
                },
            ],
        ]);
    }
}
