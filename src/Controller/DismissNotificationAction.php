<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPlausiblePlugin\Factory\NotificationDismissalFactoryInterface;
use Setono\SyliusPlausiblePlugin\Generator\ChannelConfigurationHashGeneratorInterface;
use Setono\SyliusPlausiblePlugin\Model\ChannelInterface;
use Setono\SyliusPlausiblePlugin\Repository\NotificationDismissalRepositoryInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class DismissNotificationAction
{
    use ORMTrait;

    public const CSRF_TOKEN_ID = 'setono_sylius_plausible_dismiss_notification';

    /** @param ChannelRepositoryInterface<ChannelInterface> $channelRepository */
    public function __construct(
        private readonly Security $security,
        private readonly NotificationDismissalRepositoryInterface $dismissalRepository,
        private readonly ChannelConfigurationHashGeneratorInterface $hashGenerator,
        private readonly NotificationDismissalFactoryInterface $dismissalFactory,
        private readonly ChannelRepositoryInterface $channelRepository,
        ManagerRegistry $managerRegistry,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof AdminUserInterface) {
            return new JsonResponse(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $request->headers->get('X-CSRF-Token');
        if (!is_string($token) || !$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_TOKEN_ID, $token))) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        $dismissal = $this->dismissalRepository->findByAdminUser($user);
        $dismissal ??= $this->dismissalFactory->createForAdminUser($user);

        /** @var list<ChannelInterface> $channels */
        $channels = $this->channelRepository->findAll();

        $dismissal->setConfigurationHash($this->hashGenerator->generate($channels));

        $manager = $this->getManager($dismissal);
        $manager->persist($dismissal);

        try {
            $manager->flush();
        } catch (UniqueConstraintViolationException) {
            // another request dismissed the notification for this user at the same time,
            // which is the outcome we wanted anyway
        }

        return new JsonResponse(['success' => true]);
    }
}
