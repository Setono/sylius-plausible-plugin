<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Event;

use Setono\TagBag\Tag\ScriptTag;

/**
 * This event is dispatched when the Plausible tracking library tag is created.
 * You can listen to this event to alter the tag before it is added to the tag bag.
 * Remember that the ScriptTag is a value object, so you need to create a new instance if you want to alter it.
 */
final class LibraryTagCreatedEvent
{
    public function __construct(public ScriptTag $tag)
    {
    }
}
