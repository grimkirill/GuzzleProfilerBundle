<?php

namespace Kitchen\GuzzleProfilerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kitchen_guzzle_profiler');
        $rootNode
            ->children()
                ->booleanNode('global')
                    ->defaultTrue()
                    ->info('Profiling all GuzzleHttp Clients to be found in the container')
                ->end()
                ->scalarNode('max_size')
                    ->defaultValue(65536)
                    ->info('The maximum size of body (in bytes)')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
