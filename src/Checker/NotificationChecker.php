<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Checker;

use Setono\SyliusPlausiblePlugin\Generator\ChannelConfigurationHashGeneratorInterface;
use Setono\SyliusPlausiblePlugin\Model\ChannelInterface;
use Setono\SyliusPlausiblePlugin\Repository\NotificationDismissalRepositoryInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class NotificationChecker implements NotificationCheckerInterface
{
    /** @param ChannelRepositoryInterface<ChannelInterface> $channelRepository */
    public function __construct(
        private readonly NotificationDismissalRepositoryInterface $dismissalRepository,
        private readonly Security $security,
        private readonly ChannelConfigurationHashGeneratorInterface $hashGenerator,
        private readonly ChannelRepositoryInterface $channelRepository,
    ) {
    }

    public function shouldShowNotification(): bool
    {
        // the only check that costs nothing, so it stays first
        $user = $this->security->getUser();
        if (!$user instanceof AdminUserInterface) {
            return false;
        }

        /** @var list<ChannelInterface> $channels */
        $channels = $this->channelRepository->findAll();

        // in a fully configured store this is where we return, having run a single query
        if (self::areAllChannelsConfigured($channels)) {
            return false;
        }

        $currentHash = $this->hashGenerator->generate($channels);
        $dismissal = $this->dismissalRepository->findValidDismissal($user, $currentHash);

        return null === $dismissal;
    }

    /**
     * @param list<ChannelInterface> $channels
     */
    private static function areAllChannelsConfigured(array $channels): bool
    {
        foreach ($channels as $channel) {
            // a disabled channel doesn't serve any traffic, so it can't be missing tracking
            if (!$channel->isEnabled()) {
                continue;
            }

            $identifier = $channel->getPlausibleScriptIdentifier();
            if (null === $identifier || '' === $identifier) {
                return false;
            }
        }

        return true;
    }
}
