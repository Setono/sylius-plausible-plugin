<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Generator;

use Setono\SyliusPlausiblePlugin\Model\ChannelInterface;

interface ChannelConfigurationHashGeneratorInterface
{
    /**
     * Generates a SHA256 hash from the given channels' Plausible configurations.
     * This hash changes when any channel's Plausible configuration is modified.
     *
     * @param list<ChannelInterface> $channels
     */
    public function generate(array $channels): string;
}
