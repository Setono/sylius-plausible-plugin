<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\SyliusPlausiblePlugin\DependencyInjection\SetonoSyliusPlausibleExtension;
use Setono\SyliusPlausiblePlugin\EventSubscriber\AddressSubscriber;
use Setono\SyliusPlausiblePlugin\EventSubscriber\AdminMenuSubscriber;
use Setono\SyliusPlausiblePlugin\EventSubscriber\BeginCheckoutSubscriber;
use Setono\SyliusPlausiblePlugin\EventSubscriber\PlausibleEventSubscriber;
use Setono\SyliusPlausiblePlugin\EventSubscriber\PlausibleLibrarySubscriber;
use Setono\SyliusPlausiblePlugin\EventSubscriber\PurchaseSubscriber;
use Setono\SyliusPlausiblePlugin\EventSubscriber\SelectPaymentMethodSubscriber;
use Setono\SyliusPlausiblePlugin\EventSubscriber\SelectShippingMethodSubscriber;
use Setono\SyliusPlausiblePlugin\Form\Type\ChannelPlausibleType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

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
    public function it_registers_plausible_library_subscriber_service(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService(PlausibleLibrarySubscriber::class);
    }

    /**
     * The tracking subscribers swallow every exception, so without a logger any failure
     * disappears without a trace. These services are not autoconfigured, which means the
     * setLogger call has to be wired explicitly.
     *
     * @test
     *
     * @dataProvider loggerAwareSubscribers
     *
     * @param class-string $serviceId
     */
    public function it_injects_a_logger_into_the_tracking_subscribers(string $serviceId): void
    {
        $this->load();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall($serviceId, 'setLogger', [
            new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
        ]);
    }

    /**
     * @return iterable<array-key, array{class-string}>
     */
    public static function loggerAwareSubscribers(): iterable
    {
        yield [AddressSubscriber::class];
        yield [BeginCheckoutSubscriber::class];
        yield [PlausibleEventSubscriber::class];
        yield [PurchaseSubscriber::class];
        yield [SelectPaymentMethodSubscriber::class];
        yield [SelectShippingMethodSubscriber::class];
    }
}
