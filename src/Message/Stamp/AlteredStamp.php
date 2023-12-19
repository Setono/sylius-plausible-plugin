<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Message\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * This stamp is added to the message in the middleware, and it is used to determine whether the event was
 * dispatched on the event dispatcher for alteration
 */
final class AlteredStamp implements StampInterface
{
}
