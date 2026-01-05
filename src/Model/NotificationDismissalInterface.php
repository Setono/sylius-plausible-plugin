<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Model;

use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface NotificationDismissalInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getAdminUser(): ?AdminUserInterface;

    public function setAdminUser(AdminUserInterface $adminUser): void;

    public function getConfigurationHash(): ?string;

    public function setConfigurationHash(string $configurationHash): void;
}
