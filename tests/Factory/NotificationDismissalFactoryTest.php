<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Factory;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPlausiblePlugin\Factory\NotificationDismissalFactory;
use Setono\SyliusPlausiblePlugin\Model\NotificationDismissalInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * @covers \Setono\SyliusPlausiblePlugin\Factory\NotificationDismissalFactory
 */
final class NotificationDismissalFactoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_creates_new_dismissal(): void
    {
        $dismissal = $this->prophesize(NotificationDismissalInterface::class);

        $decoratedFactory = $this->prophesize(FactoryInterface::class);
        $decoratedFactory->createNew()->willReturn($dismissal->reveal());

        $factory = new NotificationDismissalFactory($decoratedFactory->reveal());

        self::assertSame($dismissal->reveal(), $factory->createNew());
    }

    /**
     * @test
     */
    public function it_creates_dismissal_for_admin_user(): void
    {
        $adminUser = $this->prophesize(AdminUserInterface::class);
        $dismissal = $this->prophesize(NotificationDismissalInterface::class);
        $dismissal->setAdminUser($adminUser->reveal())->shouldBeCalled();

        $decoratedFactory = $this->prophesize(FactoryInterface::class);
        $decoratedFactory->createNew()->willReturn($dismissal->reveal());

        $factory = new NotificationDismissalFactory($decoratedFactory->reveal());

        $result = $factory->createForAdminUser($adminUser->reveal());

        self::assertSame($dismissal->reveal(), $result);
    }
}
