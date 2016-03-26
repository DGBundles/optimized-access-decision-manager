<?php
/**
 * @author Dmitry Grachikov <dgrachikov@gmail.com>
 */
namespace DG\OptimizedAccessDecisionManagerBundle\Tests\Unit;


use DG\OptimizedAccessDecisionManagerBundle\Service\AccessDecisionManagerOptimized;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AccessDecisionManagerOptimizedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccessDecisionManagerOptimized
     */
    private $accessDecisionManager;

    /**
     * @var AccessDecisionManager
     */
    private $accessDecisionManagerInnerMock;

    private $voterNoKey1;

    private $voterNoKey2;

    private $voterKey1;

    private $voterKey2_1;

    private $voterKey2_2;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->accessDecisionManagerInnerMock = $this->prophesize(AccessDecisionManager::class);

        $this->accessDecisionManager = new AccessDecisionManagerOptimized($this->accessDecisionManagerInnerMock->reveal());

        $this->voterNoKey1 = $this->prophesize(VoterInterface::class);
        $this->voterNoKey2 = $this->prophesize(VoterInterface::class);
        $this->voterKey1 = $this->prophesize(VoterInterface::class);
        $this->voterKey2_1 = $this->prophesize(VoterInterface::class);
        $this->voterKey2_2 = $this->prophesize(VoterInterface::class);

        $this->accessDecisionManager->setVoters([$this->voterNoKey1->reveal(), $this->voterNoKey2->reveal()]);
        $this->accessDecisionManager->setSpecificVoters([
            'key_1' => [$this->voterKey1],
            'key_2' => [$this->voterKey2_1, $this->voterKey2_2],
        ]);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->accessDecisionManagerInnerMock = null;
        $this->accessDecisionManager = null;
        $this->voterNoKey1 = null;
        $this->voterNoKey2 = null;
        $this->voterKey1 = null;
        $this->voterKey2_1 = null;
        $this->voterKey2_2 = null;
    }

    public function testDecideNoSecurityKey()
    {
        $this->accessDecisionManagerInnerMock->setVoters(Argument::exact([$this->voterNoKey1, $this->voterNoKey2]));

        $this->accessDecisionManager->decide(new UsernamePasswordToken('user', '', 'firewall'), [], null);
    }

    public function testDecideWithSecurityKey1()
    {
        $this->accessDecisionManagerInnerMock->setVoters(Argument::exact([$this->voterKey1]));
        $this->accessDecisionManager->decide(new UsernamePasswordToken('user', '', 'firewall'), ['securityKey' => 'key_1'], null);
    }

    public function testDecideWithSecurityKey2()
    {
        $this->accessDecisionManagerInnerMock->setVoters(Argument::exact([$this->voterKey2_1, $this->voterKey2_2]));
        $this->accessDecisionManager->decide(new UsernamePasswordToken('user', '', 'firewall'), ['securityKey' => 'key_2'], null);
    }

    public function testDecideWithWrongSecurityKey()
    {
        $this->accessDecisionManagerInnerMock->setVoters(Argument::exact([]));
        $this->accessDecisionManager->decide(new UsernamePasswordToken('user', '', 'firewall'), ['securityKey' => 'key_wrong'], null);
    }
}
