<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Checker;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Setono\SyliusPlausiblePlugin\Checker\NotificationChecker;
use Setono\SyliusPlausiblePlugin\Generator\ChannelConfigurationHashGeneratorInterface;
use Setono\SyliusPlausiblePlugin\Model\NotificationDismissalInterface;
use Setono\SyliusPlausiblePlugin\Repository\NotificationDismissalRepositoryInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\Channel;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @covers \Setono\SyliusPlausiblePlugin\Checker\NotificationChecker
 */
final class NotificationCheckerTest extends TestCase
{
    use ProphecyTrait;

    private const CHANNEL_CLASS = Channel::class;

    /** @var ObjectProphecy<NotificationDismissalRepositoryInterface> */
    private ObjectProphecy $dismissalRepository;

    /** @var ObjectProphecy<Security> */
    private ObjectProphecy $security;

    /** @var ObjectProphecy<ChannelConfigurationHashGeneratorInterface> */
    private ObjectProphecy $hashGenerator;

    /** @var ObjectProphecy<ManagerRegistry> */
    private ObjectProphecy $managerRegistry;

    /** @var ObjectProphecy<EntityManagerInterface> */
    private ObjectProphecy $entityManager;

    protected function setUp(): void
    {
        $this->dismissalRepository = $this->prophesize(NotificationDismissalRepositoryInterface::class);
        $this->security = $this->prophesize(Security::class);
        $this->hashGenerator = $this->prophesize(ChannelConfigurationHashGeneratorInterface::class);
        $this->managerRegistry = $this->prophesize(ManagerRegistry::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);

        $this->managerRegistry->getManagerForClass(self::CHANNEL_CLASS)->willReturn($this->entityManager->reveal());
    }

    /**
     * @test
     */
    public function it_returns_false_when_user_is_not_admin(): void
    {
        $this->security->getUser()->willReturn(null);

        $checker = $this->createChecker();

        self::assertFalse($checker->shouldShowNotification());
    }

    /**
     * @test
     */
    public function it_returns_false_when_all_channels_are_configured(): void
    {
        $adminUser = $this->prophesize(AdminUserInterface::class);
        $this->security->getUser()->willReturn($adminUser->reveal());

        $this->mockQueryBuilderReturningCount(0);

        $checker = $this->createChecker();

        self::assertFalse($checker->shouldShowNotification());
    }

    /**
     * @test
     */
    public function it_returns_false_when_user_has_valid_dismissal(): void
    {
        $adminUser = $this->prophesize(AdminUserInterface::class);
        $this->security->getUser()->willReturn($adminUser->reveal());

        $this->mockQueryBuilderReturningCount(1);

        $dismissal = $this->prophesize(NotificationDismissalInterface::class);
        $this->dismissalRepository->findValidDismissal($adminUser->reveal(), 'hash123')->willReturn($dismissal->reveal());
        $this->hashGenerator->generate()->willReturn('hash123');

        $checker = $this->createChecker();

        self::assertFalse($checker->shouldShowNotification());
    }

    /**
     * @test
     */
    public function it_returns_true_when_channels_not_configured_and_no_valid_dismissal(): void
    {
        $adminUser = $this->prophesize(AdminUserInterface::class);
        $this->security->getUser()->willReturn($adminUser->reveal());

        $this->mockQueryBuilderReturningCount(1);

        $this->dismissalRepository->findValidDismissal($adminUser->reveal(), 'hash123')->willReturn(null);
        $this->hashGenerator->generate()->willReturn('hash123');

        $checker = $this->createChecker();

        self::assertTrue($checker->shouldShowNotification());
    }

    private function createChecker(): NotificationChecker
    {
        return new NotificationChecker(
            $this->dismissalRepository->reveal(),
            $this->security->reveal(),
            $this->hashGenerator->reveal(),
            $this->managerRegistry->reveal(),
            self::CHANNEL_CLASS,
        );
    }

    private function mockQueryBuilderReturningCount(int $count): void
    {
        $expr = $this->prophesize(Expr::class);
        $expr->isNull(Argument::any())->willReturn('o.plausibleScriptIdentifier IS NULL');
        $expr->eq(Argument::any(), Argument::any())->willReturn('o.plausibleScriptIdentifier = :empty');
        $expr->orX(Argument::any(), Argument::any())->willReturn('(o.plausibleScriptIdentifier IS NULL OR o.plausibleScriptIdentifier = :empty)');

        $query = $this->prophesize(AbstractQuery::class);
        $query->getSingleScalarResult()->willReturn($count);

        $qb = $this->prophesize(QueryBuilder::class);
        $qb->select(Argument::any())->willReturn($qb->reveal());
        $qb->from(Argument::any(), Argument::any())->willReturn($qb->reveal());
        $qb->where(Argument::any())->willReturn($qb->reveal());
        $qb->setParameter(Argument::any(), Argument::any())->willReturn($qb->reveal());
        $qb->expr()->willReturn($expr->reveal());
        $qb->getQuery()->willReturn($query->reveal());

        $this->entityManager->createQueryBuilder()->willReturn($qb->reveal());
    }
}
