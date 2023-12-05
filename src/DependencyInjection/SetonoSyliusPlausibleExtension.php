<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SetonoSyliusPlausibleExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        /**
         * @psalm-suppress PossiblyNullArgument
         *
         * @var array{client_side: array{enabled: bool, script: string}, domain: string} $config
         */
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $container->setParameter('setono_sylius_plausible.client_side.script', $config['client_side']['script']);
        $container->setParameter('setono_sylius_plausible.domain', $config['domain']);

        if ($config['client_side']['enabled']) {
            $loader->load('services/conditional/client_side.xml');
        }

        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'messenger' => [
                'buses' => [
                    'setono_sylius_plausible.event_bus' => null,
                ],
            ],
        ]);
    }
}
