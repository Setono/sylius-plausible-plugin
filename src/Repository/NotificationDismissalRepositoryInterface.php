<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Repository;

use Setono\SyliusPlausiblePlugin\Model\NotificationDismissalInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * @extends RepositoryInterface<NotificationDismissalInterface>
 */
interface NotificationDismissalRepositoryInterface extends RepositoryInterface
{
    public function findByAdminUser(AdminUserInterface $adminUser): ?NotificationDismissalInterface;

    public function findValidDismissal(AdminUserInterface $adminUser, string $configurationHash): ?NotificationDismissalInterface;
}
