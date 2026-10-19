<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Setono\SyliusPlausiblePlugin\Model\NotificationDismissal;
use Setono\SyliusPlausiblePlugin\Repository\NotificationDismissalRepository;
use Sylius\Component\Core\Model\AdminUser;

/**
 * Doctrine's findOneBy() hands the criteria to the entity persister, so that is where these tests
 * check which criteria the repository builds.
 *
 * @covers \Setono\SyliusPlausiblePlugin\Repository\NotificationDismissalRepository
 */
final class NotificationDismissalRepositoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<EntityPersister> */
    private ObjectProphecy $persister;

    private NotificationDismissalRepository $repository;

    protected function setUp(): void
    {
        $this->persister = $this->prophesize(EntityPersister::class);

        $unitOfWork = $this->prophesize(UnitOfWork::class);
        $unitOfWork->getEntityPersister(NotificationDismissal::class)->willReturn($this->persister->reveal());

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork->reveal());

        $this->repository = new NotificationDismissalRepository(
            $entityManager->reveal(),
            new ClassMetadata(NotificationDismissal::class),
        );
    }

    /**
     * @test
     */
    public function it_finds_the_dismissal_of_an_admin_user(): void
    {
        $adminUser = new AdminUser();
        $dismissal = new NotificationDismissal();

        $this->persister->load(['adminUser' => $adminUser], Argument::cetera())->willReturn($dismissal);

        self::assertSame($dismissal, $this->repository->findByAdminUser($adminUser));
    }

    /**
     * @test
     */
    public function it_finds_the_dismissal_of_an_admin_user_for_a_configuration(): void
    {
        $adminUser = new AdminUser();
        $dismissal = new NotificationDismissal();

        $this->persister->load(['adminUser' => $adminUser, 'configurationHash' => 'hash'], Argument::cetera())->willReturn($dismissal);

        self::assertSame($dismissal, $this->repository->findValidDismissal($adminUser, 'hash'));
    }
}
