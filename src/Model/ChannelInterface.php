<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Model;

use Sylius\Component\Core\Model\ChannelInterface as BaseChannelInterface;

interface ChannelInterface extends BaseChannelInterface
{
    public function getPlausibleScriptIdentifier(): ?string;

    public function setPlausibleScriptIdentifier(?string $plausibleScriptIdentifier): void;
}
