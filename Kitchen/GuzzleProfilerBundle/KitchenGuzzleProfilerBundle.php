<?php

namespace Kitchen\GuzzleProfilerBundle;

use Kitchen\GuzzleProfilerBundle\DependencyInjection\Compiler\GuzzleMiddlewarePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KitchenGuzzleProfilerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new GuzzleMiddlewarePass());
    }
}
