<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Generator;

interface ChannelConfigurationHashGeneratorInterface
{
    /**
     * Generates a SHA256 hash from all channels' Plausible configurations.
     * This hash changes when any channel's Plausible configuration is modified.
     */
    public function generate(): string;
}
