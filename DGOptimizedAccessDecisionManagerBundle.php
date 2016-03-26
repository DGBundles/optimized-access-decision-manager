<?php

namespace DG\OptimizedAccessDecisionManagerBundle;

use DG\OptimizedAccessDecisionManagerBundle\DependencyInjection\CompilerPass\RegisterVotersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DGOptimizedAccessDecisionManagerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterVotersPass());
    }
}
