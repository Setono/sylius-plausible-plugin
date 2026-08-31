<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Generator;

final class ChannelConfigurationHashGenerator implements ChannelConfigurationHashGeneratorInterface
{
    public function generate(array $channels): string
    {
        $configParts = [];

        foreach ($channels as $channel) {
            $code = $channel->getCode();
            if (null === $code) {
                continue;
            }

            $configParts[$code] = $channel->getPlausibleScriptIdentifier() ?? '';
        }

        ksort($configParts);

        return hash('sha256', serialize($configParts));
    }
}
