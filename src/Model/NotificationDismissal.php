<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Model;

use Sylius\Component\Core\Model\AdminUserInterface;

class NotificationDismissal implements NotificationDismissalInterface
{
    protected ?int $id = null;

    protected ?AdminUserInterface $adminUser = null;

    protected ?string $configurationHash = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdminUser(): ?AdminUserInterface
    {
        return $this->adminUser;
    }

    public function setAdminUser(AdminUserInterface $adminUser): void
    {
        $this->adminUser = $adminUser;
    }

    public function getConfigurationHash(): ?string
    {
        return $this->configurationHash;
    }

    public function setConfigurationHash(string $configurationHash): void
    {
        $this->configurationHash = $configurationHash;
    }
}
