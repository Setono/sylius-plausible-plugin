<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Factory;

use Setono\SyliusPlausiblePlugin\Model\NotificationDismissalInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class NotificationDismissalFactory implements NotificationDismissalFactoryInterface
{
    /** @param FactoryInterface<NotificationDismissalInterface> $decoratedFactory */
    public function __construct(
        private readonly FactoryInterface $decoratedFactory,
    ) {
    }

    public function createNew(): NotificationDismissalInterface
    {
        /** @var NotificationDismissalInterface $dismissal */
        $dismissal = $this->decoratedFactory->createNew();

        return $dismissal;
    }

    public function createForAdminUser(AdminUserInterface $adminUser): NotificationDismissalInterface
    {
        $dismissal = $this->createNew();
        $dismissal->setAdminUser($adminUser);

        return $dismissal;
    }
}
