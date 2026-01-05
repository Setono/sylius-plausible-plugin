<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Checker;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPlausiblePlugin\Generator\ChannelConfigurationHashGeneratorInterface;
use Setono\SyliusPlausiblePlugin\Repository\NotificationDismissalRepositoryInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class NotificationChecker implements NotificationCheckerInterface
{
    use ORMTrait;

    /** @param class-string $channelClass */
    public function __construct(
        private readonly NotificationDismissalRepositoryInterface $dismissalRepository,
        private readonly Security $security,
        private readonly ChannelConfigurationHashGeneratorInterface $hashGenerator,
        ManagerRegistry $managerRegistry,
        private readonly string $channelClass,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function shouldShowNotification(): bool
    {
        $user = $this->security->getUser();
        if (!$user instanceof AdminUserInterface) {
            return false;
        }

        if ($this->areAllChannelsConfigured()) {
            return false;
        }

        $currentHash = $this->hashGenerator->generate();
        $dismissal = $this->dismissalRepository->findValidDismissal($user, $currentHash);

        return null === $dismissal;
    }

    private function areAllChannelsConfigured(): bool
    {
        $qb = $this->getManager($this->channelClass)->createQueryBuilder();

        $count = (int) $qb
            ->select('COUNT(o.id)')
            ->from($this->channelClass, 'o')
            ->where($qb->expr()->orX(
                $qb->expr()->isNull('o.plausibleScriptIdentifier'),
                $qb->expr()->eq('o.plausibleScriptIdentifier', ':empty'),
            ))
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return 0 === $count;
    }
}
