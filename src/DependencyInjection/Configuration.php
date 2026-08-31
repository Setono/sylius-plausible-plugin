<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\DependencyInjection;

use Setono\SyliusPlausiblePlugin\Model\NotificationDismissal;
use Setono\SyliusPlausiblePlugin\Repository\NotificationDismissalRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Bundle\ResourceBundle\Form\Type\DefaultResourceType;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public const DEFAULT_SCRIPT_HOST = 'https://plausible.io';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('setono_sylius_plausible');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode->canBeDisabled();

        $this->addScriptHostSection($rootNode);
        $this->addResourcesSection($rootNode);

        return $treeBuilder;
    }

    private function addScriptHostSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->scalarNode('script_host')
                    ->info('The host serving the Plausible script. Change this if you self host Plausible, i.e. Plausible Community Edition.')
                    ->example('https://analytics.example.com')
                    ->defaultValue(self::DEFAULT_SCRIPT_HOST)
                    ->cannotBeEmpty()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static fn (string $value): string => rtrim(trim($value), '/'))
                    ->end()
                    ->validate()
                        ->ifTrue(static fn (string $value): bool => !str_starts_with($value, 'http://') && !str_starts_with($value, 'https://'))
                        ->thenInvalid('The script host must be an absolute URL including the scheme, i.e. https://plausible.io. Got %s')
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addResourcesSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('notification_dismissal')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('model')->defaultValue(NotificationDismissal::class)->cannotBeEmpty()->end()
                                    ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                    ->scalarNode('factory')->defaultValue(Factory::class)->end()
                                    ->scalarNode('repository')->defaultValue(NotificationDismissalRepository::class)->end()
                                    ->scalarNode('form')->defaultValue(DefaultResourceType::class)->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
