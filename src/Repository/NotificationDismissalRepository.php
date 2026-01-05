<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Repository;

use Setono\SyliusPlausiblePlugin\Model\NotificationDismissalInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\AdminUserInterface;

class NotificationDismissalRepository extends EntityRepository implements NotificationDismissalRepositoryInterface
{
    public function findByAdminUser(AdminUserInterface $adminUser): ?NotificationDismissalInterface
    {
        /** @var NotificationDismissalInterface|null $result */
        $result = $this->findOneBy(['adminUser' => $adminUser]);

        return $result;
    }

    public function findValidDismissal(AdminUserInterface $adminUser, string $configurationHash): ?NotificationDismissalInterface
    {
        /** @var NotificationDismissalInterface|null $result */
        $result = $this->findOneBy([
            'adminUser' => $adminUser,
            'configurationHash' => $configurationHash,
        ]);

        return $result;
    }
}
