<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\SyliusPlausiblePlugin\DependencyInjection\SetonoSyliusPlausibleExtension;
use Setono\SyliusPlausiblePlugin\EventSubscriber\AdminMenuSubscriber;
use Setono\SyliusPlausiblePlugin\Form\Type\ChannelPlausibleType;

/**
 * See examples of tests and configuration options here: https://github.com/SymfonyTest/SymfonyDependencyInjectionTest
 */
final class SetonoSyliusPlausibleExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new SetonoSyliusPlausibleExtension(),
        ];
    }

    /**
     * @test
     */
    public function it_registers_channel_plausible_type_service(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService(ChannelPlausibleType::class);
    }

    /**
     * @test
     */
    public function it_registers_admin_menu_subscriber_service(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService(AdminMenuSubscriber::class);
    }

    /**
     * @test
     */
    public function it_loads_client_side_services_by_default(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService(\Setono\SyliusPlausiblePlugin\EventSubscriber\ClientSide\LibrarySubscriber::class);
    }

    /**
     * @test
     */
    public function it_does_not_load_client_side_services_when_disabled(): void
    {
        $this->load(['client_side' => ['enabled' => false]]);

        $this->assertContainerBuilderNotHasService(\Setono\SyliusPlausiblePlugin\EventSubscriber\ClientSide\LibrarySubscriber::class);
    }
}
