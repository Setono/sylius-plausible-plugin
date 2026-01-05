<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Factory;

use Setono\SyliusPlausiblePlugin\Model\NotificationDismissalInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * @extends FactoryInterface<NotificationDismissalInterface>
 */
interface NotificationDismissalFactoryInterface extends FactoryInterface
{
    public function createForAdminUser(AdminUserInterface $adminUser): NotificationDismissalInterface;
}
