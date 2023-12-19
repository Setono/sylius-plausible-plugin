<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Event;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;

/**
 * This event is dispatched in the middleware on the event bus, and you can listen to it to populate the event with data
 */
final class PopulateEvent
{
    public function __construct(public readonly Event $event)
    {
    }
}
