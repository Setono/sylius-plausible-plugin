<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('setono_sylius_plausible');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        /** @psalm-suppress MixedMethodCall,PossiblyNullReference,UndefinedInterfaceMethod,PossiblyUndefinedMethod */
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('client_side')
                    ->canBeDisabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('script')
                            ->info('The Plausible script to use. See available scripts here: https://plausible.io/docs/script-extensions')
                            ->defaultValue('https://plausible.io/js/script.revenue.js')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('domain')
                    ->info('The domain to use for the tracking script. If not set, the domain will be inferred from the request. This is useful for testing purposes when you want to test the implementation on a different domain than the one you are currently on')
                    ->defaultNull()
                    ->cannotBeEmpty()
        ;

        return $treeBuilder;
    }
}
