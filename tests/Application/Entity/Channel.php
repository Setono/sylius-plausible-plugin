<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPlausiblePlugin\Model\ChannelInterface as PlausibleChannelInterface;
use Setono\SyliusPlausiblePlugin\Model\ChannelTrait as PlausibleChannelTrait;
use Sylius\Component\Core\Model\Channel as BaseChannel;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="sylius_channel")
 */
class Channel extends BaseChannel implements PlausibleChannelInterface
{
    use PlausibleChannelTrait;
}
