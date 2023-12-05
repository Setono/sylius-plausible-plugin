<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Event;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;

/**
 * This event is dispatched before the event is sent to Plausible.
 * This is the event you want to listen to if you want to change the event details before sending the event to Plausible
 */
final class PreSendEvent
{
    /**
     * If this is set to false, the event will not be sent to Plausible
     */
    public bool $send = true;

    public function __construct(public readonly Event $event)
    {
    }
}
