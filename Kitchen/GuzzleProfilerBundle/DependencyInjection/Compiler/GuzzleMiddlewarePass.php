<?php

namespace Kitchen\GuzzleProfilerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class GuzzleMiddlewarePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('profiler')) {
            $configurator = $container->getDefinition('kitchen_guzzle_profiler.configurator');
            $taggedServices = $container->findTaggedServiceIds('guzzle_profiler');
            foreach ($taggedServices as $id => $tags) {
                $container->getDefinition($id)->setConfigurator([$configurator, 'configure']);
            }
        }
    }
}
