<?php
/**
 * @author Dmitry Grachikov <dgrachikov@gmail.com>
 */

namespace DG\OptimizedAccessDecisionManagerBundle\DependencyInjection\CompilerPass;


use Symfony\Bundle\SecurityBundle\DependencyInjection\Compiler\AddSecurityVotersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class RegisterVotersPass extends AddSecurityVotersPass
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        parent::process($container);
        $this->addSpecificVoters($container);
    }

    private function addSpecificVoters(ContainerBuilder $container)
    {
        $voters = [];

        foreach ($container->findTaggedServiceIds('security.specific_voter') as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['securityKey'])) {
                    throw new LogicException('security.specific_voter tag must have "securityKey" attribute');
                }
                $key = $tag['securityKey'];

                if (!isset($voters[$key])) {
                    $voters[$key] = [];
                }

                $voters[$key][] = new Reference($id);
            }
        }

        if ($voters) {
            $container->getDefinition('dg.security.access.decision_manager_optimized')->addMethodCall('setSpecificVoters', array($voters));
        }
    }
}