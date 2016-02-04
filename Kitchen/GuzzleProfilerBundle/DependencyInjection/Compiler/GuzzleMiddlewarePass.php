<?php

namespace Kitchen\GuzzleProfilerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class GuzzleMiddlewarePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('profiler')) {
            $taggedServices = $container->findTaggedServiceIds('guzzle_profiler');
            $doneIds = [];
            foreach ($taggedServices as $id => $tags) {
                $doneIds[$id] = true;
                $this->injectProfiler($container, $id);
            }
            if ($container->getParameter('kitchen_guzzle_profiler.compiler.global')) {
                foreach ($container->getServiceIds() as $id) {
                    if (!array_key_exists($id, $doneIds) && $container->hasDefinition($id)) {
                        $def = $container->getDefinition($id);
                        if ($def->getClass() && class_exists($def->getClass())) {
                            if (in_array("GuzzleHttp\\ClientInterface", class_implements($def->getClass()))) {
                                $this->injectProfiler($container, $id);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function injectProfiler(ContainerBuilder $container, $id)
    {
        $configurator = $container->getDefinition('kitchen_guzzle_profiler.configurator');
        if (!$container->getDefinition($id)->getConfigurator()) {
            $container->getDefinition($id)->setConfigurator([$configurator, 'configure']);
        } else {
            $configuratorClone = $configurator;
            $configuratorClone->addMethodCall('setNextConfigurator', $container->getDefinition($id)->getConfigurator());
            $container->addDefinitions([
                'kitchen_guzzle_profiler.configurator_' . $id => $configuratorClone
            ]);
            $container->getDefinition($id)->setConfigurator([$configuratorClone, 'configure']);
        }
    }
}
