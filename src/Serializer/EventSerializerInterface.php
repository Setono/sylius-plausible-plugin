<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Serializer;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;

interface EventSerializerInterface
{
    public const CONTEXT_CLIENT_SIDE = 'client_side';

    public const CONTEXT_SERVER_SIDE = 'server_side';

    /**
     * @param self::CONTEXT_* $context
     */
    public function serialize(Event $event, string $context): string;
}
