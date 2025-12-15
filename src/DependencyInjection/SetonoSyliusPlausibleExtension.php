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
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('sylius_grid', [
            'grids' => [
                'setono_sylius_plausible_admin_channel' => [
                    'driver' => [
                        'name' => 'doctrine/orm',
                        'options' => [
                            'class' => '%sylius.model.channel.class%',
                        ],
                    ],
                    'sorting' => [
                        'name' => 'asc',
                    ],
                    'fields' => [
                        'name' => [
                            'type' => 'string',
                            'label' => 'sylius.ui.name',
                            'sortable' => null,
                        ],
                        'code' => [
                            'type' => 'string',
                            'label' => 'sylius.ui.code',
                            'sortable' => null,
                        ],
                        'hostname' => [
                            'type' => 'string',
                            'label' => 'sylius.ui.hostname',
                            'sortable' => null,
                        ],
                        'plausibleStatus' => [
                            'type' => 'twig',
                            'label' => 'setono_sylius_plausible.ui.plausible_status',
                            'path' => '.',
                            'options' => [
                                'template' => '@SetonoSyliusPlausiblePlugin/admin/channel/grid/field/plausible_status.html.twig',
                            ],
                        ],
                    ],
                    'actions' => [
                        'item' => [
                            'update' => [
                                'type' => 'update',
                                'label' => 'setono_sylius_plausible.ui.configure',
                                'options' => [
                                    'link' => [
                                        'route' => 'setono_sylius_plausible_admin_channel_update',
                                        'parameters' => [
                                            'id' => 'resource.id',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
