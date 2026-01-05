<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPlausiblePlugin\Factory\NotificationDismissalFactoryInterface;
use Setono\SyliusPlausiblePlugin\Generator\ChannelConfigurationHashGeneratorInterface;
use Setono\SyliusPlausiblePlugin\Repository\NotificationDismissalRepositoryInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class DismissNotificationAction
{
    use ORMTrait;

    public function __construct(
        private readonly Security $security,
        private readonly NotificationDismissalRepositoryInterface $dismissalRepository,
        private readonly ChannelConfigurationHashGeneratorInterface $hashGenerator,
        private readonly NotificationDismissalFactoryInterface $dismissalFactory,
        ManagerRegistry $managerRegistry,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof AdminUserInterface) {
            return new JsonResponse(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $dismissal = $this->dismissalRepository->findByAdminUser($user);

        if (null === $dismissal) {
            $dismissal = $this->dismissalFactory->createForAdminUser($user);
        }

        $dismissal->setConfigurationHash($this->hashGenerator->generate());

        $manager = $this->getManager($dismissal);
        $manager->persist($dismissal);
        $manager->flush();

        return new JsonResponse(['success' => true]);
    }
}
