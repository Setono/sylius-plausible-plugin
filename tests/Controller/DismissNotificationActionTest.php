<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPlausiblePlugin\Controller\DismissNotificationAction;
use Setono\SyliusPlausiblePlugin\Factory\NotificationDismissalFactoryInterface;
use Setono\SyliusPlausiblePlugin\Generator\ChannelConfigurationHashGeneratorInterface;
use Setono\SyliusPlausiblePlugin\Model\NotificationDismissalInterface;
use Setono\SyliusPlausiblePlugin\Repository\NotificationDismissalRepositoryInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \Setono\SyliusPlausiblePlugin\Controller\DismissNotificationAction
 */
final class DismissNotificationActionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_returns_unauthorized_when_user_is_not_logged_in(): void
    {
        $security = $this->prophesize(Security::class);
        $security->getUser()->willReturn(null);

        $dismissalRepository = $this->prophesize(NotificationDismissalRepositoryInterface::class);
        $hashGenerator = $this->prophesize(ChannelConfigurationHashGeneratorInterface::class);
        $dismissalFactory = $this->prophesize(NotificationDismissalFactoryInterface::class);
        $managerRegistry = $this->prophesize(ManagerRegistry::class);

        $action = new DismissNotificationAction(
            $security->reveal(),
            $dismissalRepository->reveal(),
            $hashGenerator->reveal(),
            $dismissalFactory->reveal(),
            $managerRegistry->reveal(),
        );

        $response = $action();

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_creates_new_dismissal_when_none_exists(): void
    {
        $adminUser = $this->prophesize(AdminUserInterface::class);

        $security = $this->prophesize(Security::class);
        $security->getUser()->willReturn($adminUser->reveal());

        $dismissalRepository = $this->prophesize(NotificationDismissalRepositoryInterface::class);
        $dismissalRepository->findByAdminUser($adminUser->reveal())->willReturn(null);

        $hashGenerator = $this->prophesize(ChannelConfigurationHashGeneratorInterface::class);
        $hashGenerator->generate()->willReturn('hash123');

        $dismissal = $this->prophesize(NotificationDismissalInterface::class);
        $dismissal->setConfigurationHash('hash123')->shouldBeCalled();

        $dismissalFactory = $this->prophesize(NotificationDismissalFactoryInterface::class);
        $dismissalFactory->createForAdminUser($adminUser->reveal())->willReturn($dismissal->reveal());

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->persist($dismissal->reveal())->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::any())->willReturn($entityManager->reveal());

        $action = new DismissNotificationAction(
            $security->reveal(),
            $dismissalRepository->reveal(),
            $hashGenerator->reveal(),
            $dismissalFactory->reveal(),
            $managerRegistry->reveal(),
        );

        $response = $action();

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        self::assertIsArray($content);
        self::assertTrue($content['success']);
    }

    /**
     * @test
     */
    public function it_updates_existing_dismissal(): void
    {
        $adminUser = $this->prophesize(AdminUserInterface::class);

        $security = $this->prophesize(Security::class);
        $security->getUser()->willReturn($adminUser->reveal());

        $dismissal = $this->prophesize(NotificationDismissalInterface::class);
        $dismissal->setConfigurationHash('hash456')->shouldBeCalled();

        $dismissalRepository = $this->prophesize(NotificationDismissalRepositoryInterface::class);
        $dismissalRepository->findByAdminUser($adminUser->reveal())->willReturn($dismissal->reveal());

        $hashGenerator = $this->prophesize(ChannelConfigurationHashGeneratorInterface::class);
        $hashGenerator->generate()->willReturn('hash456');

        $dismissalFactory = $this->prophesize(NotificationDismissalFactoryInterface::class);

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->persist($dismissal->reveal())->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::any())->willReturn($entityManager->reveal());

        $action = new DismissNotificationAction(
            $security->reveal(),
            $dismissalRepository->reveal(),
            $hashGenerator->reveal(),
            $dismissalFactory->reveal(),
            $managerRegistry->reveal(),
        );

        $response = $action();

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        self::assertIsArray($content);
        self::assertTrue($content['success']);
    }
}
