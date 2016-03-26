<?php
/**
 * @author Dmitry Grachikov <dgrachikov@gmail.com>
 */

namespace DG\OptimizedAccessDecisionManagerBundle\Tests\Unit\CompilerPass;


use DG\OptimizedAccessDecisionManagerBundle\DependencyInjection\CompilerPass\RegisterVotersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterVotersPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RegisterVotersPass
     */
    private $compiler;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $containerMock;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $definitionMock;

    protected function setUp()
    {
        $this->containerMock = $this->prophesize(ContainerBuilder::class);
        $this->definitionMock = $this->prophesize(Definition::class);
        $this->compiler = new RegisterVotersPass();
    }

    protected function tearDown()
    {
        $this->containerMock = null;
        $this->definitionMock = null;
        $this->compiler = null;
    }

    public function testProcessingOfSpecificVoters()
    {
        $this->containerMock->hasDefinition('security.access.decision_manager')->willReturn(false);

        $this->containerMock->findTaggedServiceIds('security.specific_voter')
            ->shouldBeCalled()
            ->willReturn([
                'voter_1' => [
                    ['securityKey' => 'voter_1_key'],
                ],
                'voter_2' => [
                    ['securityKey' => 'voter_2_key_1'],
                    ['securityKey' => 'voter_2_key_2'],
                ],
                'voter_3' => [
                    ['securityKey' => 'voter_1_key'],
                ],
            ]);

        $this->containerMock->getDefinition('dg.security.access.decision_manager_optimized')
            ->shouldBeCalled()
            ->willReturn($this->definitionMock->reveal());

        $this->definitionMock->addMethodCall('setSpecificVoters', [[
                'voter_1_key' => [
                    new Reference('voter_1'),
                    new Reference('voter_3'),
                ],
                'voter_2_key_1' => [
                    new Reference('voter_2'),
                ],
                'voter_2_key_2' => [
                    new Reference('voter_2'),
                ],
            ]])
            ->shouldBeCalled();

        $this->compiler->process($this->containerMock->reveal());
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\LogicException
     */
    public function testFailingOfCompiling()
    {
        $this->containerMock->hasDefinition('security.access.decision_manager')->willReturn(false);

        $this->containerMock->findTaggedServiceIds('security.specific_voter')
            ->shouldBeCalled()
            ->willReturn([
                'voter_1' => [
                    []
                ],
            ]);

        $this->compiler->process($this->containerMock->reveal());
    }
}
