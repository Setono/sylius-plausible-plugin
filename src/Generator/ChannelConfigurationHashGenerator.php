<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Generator;

use Setono\SyliusPlausiblePlugin\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;

final class ChannelConfigurationHashGenerator implements ChannelConfigurationHashGeneratorInterface
{
    /** @param ChannelRepositoryInterface<ChannelInterface> $channelRepository */
    public function __construct(
        private readonly ChannelRepositoryInterface $channelRepository,
    ) {
    }

    public function generate(): string
    {
        /** @var list<ChannelInterface> $channels */
        $channels = $this->channelRepository->findAll();

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
