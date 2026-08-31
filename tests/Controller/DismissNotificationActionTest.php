<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Controller;

use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Query;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Setono\SyliusPlausiblePlugin\Controller\DismissNotificationAction;
use Setono\SyliusPlausiblePlugin\Factory\NotificationDismissalFactoryInterface;
use Setono\SyliusPlausiblePlugin\Generator\ChannelConfigurationHashGeneratorInterface;
use Setono\SyliusPlausiblePlugin\Model\NotificationDismissalInterface;
use Setono\SyliusPlausiblePlugin\Repository\NotificationDismissalRepositoryInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

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
        $csrfTokenManager = $this->validCsrfTokenManager();

        $action = new DismissNotificationAction(
            $security->reveal(),
            $dismissalRepository->reveal(),
            $hashGenerator->reveal(),
            $dismissalFactory->reveal(),
            $managerRegistry->reveal(),
            $csrfTokenManager->reveal(),
        );

        $response = $action($this->requestWithToken());

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

        $csrfTokenManager = $this->validCsrfTokenManager();

        $action = new DismissNotificationAction(
            $security->reveal(),
            $dismissalRepository->reveal(),
            $hashGenerator->reveal(),
            $dismissalFactory->reveal(),
            $managerRegistry->reveal(),
            $csrfTokenManager->reveal(),
        );

        $response = $action($this->requestWithToken());

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        self::assertIsArray($content);
        self::assertTrue($content['success']);
    }

    /**
     * The dismissal table has a unique constraint on the admin user, so two concurrent
     * dismissals race. Losing that race still leaves the notification dismissed.
     *
     * @test
     */
    public function it_succeeds_when_a_concurrent_request_already_created_the_dismissal(): void
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
        $entityManager->flush()->willThrow(new UniqueConstraintViolationException(
            $this->prophesize(DriverException::class)->reveal(),
            new Query('', [], []),
        ));

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::any())->willReturn($entityManager->reveal());

        $csrfTokenManager = $this->validCsrfTokenManager();

        $action = new DismissNotificationAction(
            $security->reveal(),
            $dismissalRepository->reveal(),
            $hashGenerator->reveal(),
            $dismissalFactory->reveal(),
            $managerRegistry->reveal(),
            $csrfTokenManager->reveal(),
        );

        $response = $action($this->requestWithToken());

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
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

        $csrfTokenManager = $this->validCsrfTokenManager();

        $action = new DismissNotificationAction(
            $security->reveal(),
            $dismissalRepository->reveal(),
            $hashGenerator->reveal(),
            $dismissalFactory->reveal(),
            $managerRegistry->reveal(),
            $csrfTokenManager->reveal(),
        );

        $response = $action($this->requestWithToken());

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true);
        self::assertIsArray($content);
        self::assertTrue($content['success']);
    }

    /**
     * @test
     */
    public function it_returns_forbidden_when_the_csrf_token_is_invalid(): void
    {
        $adminUser = $this->prophesize(AdminUserInterface::class);

        $security = $this->prophesize(Security::class);
        $security->getUser()->willReturn($adminUser->reveal());

        $dismissalRepository = $this->prophesize(NotificationDismissalRepositoryInterface::class);
        $dismissalRepository->findByAdminUser(Argument::any())->shouldNotBeCalled();

        $hashGenerator = $this->prophesize(ChannelConfigurationHashGeneratorInterface::class);
        $dismissalFactory = $this->prophesize(NotificationDismissalFactoryInterface::class);
        $managerRegistry = $this->prophesize(ManagerRegistry::class);

        $csrfTokenManager = $this->prophesize(CsrfTokenManagerInterface::class);
        $csrfTokenManager->isTokenValid(Argument::type(CsrfToken::class))->willReturn(false);

        $action = new DismissNotificationAction(
            $security->reveal(),
            $dismissalRepository->reveal(),
            $hashGenerator->reveal(),
            $dismissalFactory->reveal(),
            $managerRegistry->reveal(),
            $csrfTokenManager->reveal(),
        );

        $response = $action($this->requestWithToken('wrong-token'));

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_forbidden_when_the_csrf_token_is_missing(): void
    {
        $adminUser = $this->prophesize(AdminUserInterface::class);

        $security = $this->prophesize(Security::class);
        $security->getUser()->willReturn($adminUser->reveal());

        $dismissalRepository = $this->prophesize(NotificationDismissalRepositoryInterface::class);
        $dismissalRepository->findByAdminUser(Argument::any())->shouldNotBeCalled();

        $hashGenerator = $this->prophesize(ChannelConfigurationHashGeneratorInterface::class);
        $dismissalFactory = $this->prophesize(NotificationDismissalFactoryInterface::class);
        $managerRegistry = $this->prophesize(ManagerRegistry::class);

        $csrfTokenManager = $this->prophesize(CsrfTokenManagerInterface::class);
        $csrfTokenManager->isTokenValid(Argument::any())->shouldNotBeCalled();

        $action = new DismissNotificationAction(
            $security->reveal(),
            $dismissalRepository->reveal(),
            $hashGenerator->reveal(),
            $dismissalFactory->reveal(),
            $managerRegistry->reveal(),
            $csrfTokenManager->reveal(),
        );

        $response = $action(new Request());

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @return ObjectProphecy<CsrfTokenManagerInterface>
     */
    private function validCsrfTokenManager(): ObjectProphecy
    {
        $csrfTokenManager = $this->prophesize(CsrfTokenManagerInterface::class);
        $csrfTokenManager->isTokenValid(Argument::type(CsrfToken::class))->willReturn(true);

        return $csrfTokenManager;
    }

    private function requestWithToken(string $token = 'valid-token'): Request
    {
        $request = new Request();
        $request->headers->set('X-CSRF-Token', $token);

        return $request;
    }
}
