<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPlausiblePlugin\Model\NotificationDismissal;
use Sylius\Component\Core\Model\AdminUser;

/**
 * @covers \Setono\SyliusPlausiblePlugin\Model\NotificationDismissal
 */
final class NotificationDismissalTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_empty_when_created(): void
    {
        $dismissal = new NotificationDismissal();

        self::assertNull($dismissal->getId());
        self::assertNull($dismissal->getAdminUser());
        self::assertNull($dismissal->getConfigurationHash());
    }

    /**
     * @test
     */
    public function it_sets_the_admin_user(): void
    {
        $adminUser = new AdminUser();
        $dismissal = new NotificationDismissal();

        $dismissal->setAdminUser($adminUser);

        self::assertSame($adminUser, $dismissal->getAdminUser());
    }

    /**
     * @test
     */
    public function it_sets_the_configuration_hash(): void
    {
        $dismissal = new NotificationDismissal();

        $dismissal->setConfigurationHash('hash');

        self::assertSame('hash', $dismissal->getConfigurationHash());
    }
}
