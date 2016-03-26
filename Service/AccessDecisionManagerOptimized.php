<?php
/**
 * @author Dmitry Grachikov <dgrachikov@gmail.com>
 */

namespace DG\OptimizedAccessDecisionManagerBundle\Service;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AccessDecisionManagerOptimized implements AccessDecisionManagerInterface
{
    const SECURITY_KEY_ATTRIBUTE = 'securityKey';

    /**
     * @var AccessDecisionManagerOptimized
     */
    private $accessDecisionManager;

    /**
     * @var array
     */
    private $voters = ['untagged' => []];

    /**
     * TaggedAccessDecisionManager constructor.
     * @param AccessDecisionManager $accessDecisionManager
     */
    public function __construct(AccessDecisionManager $accessDecisionManager)
    {
        $this->accessDecisionManager = $accessDecisionManager;
    }

    /**
     * Configures the voters.
     *
     * @param VoterInterface[] $voters An array of VoterInterface instances
     */
    public function setVoters(array $voters)
    {
        $this->voters['untagged'] = $voters;
    }

    /**
     * Configures the voters.
     *
     * @param array $specificVoters
     */
    public function setSpecificVoters(array $specificVoters)
    {
        $this->voters = $this->voters + $specificVoters;
    }

    /**
     * Decides whether the access is possible or not.
     *
     * @param TokenInterface $token A TokenInterface instance
     * @param array $attributes An array of attributes associated with the method being invoked
     * @param object $object The object to secure
     *
     * @return bool true if the access is granted, false otherwise
     */
    public function decide(TokenInterface $token, array $attributes, $object = null)
    {
        if (!empty($attributes[static::SECURITY_KEY_ATTRIBUTE])) {
            if (isset($this->voters[$attributes[static::SECURITY_KEY_ATTRIBUTE]])) {
                $this->accessDecisionManager->setVoters($this->voters[$attributes[static::SECURITY_KEY_ATTRIBUTE]]);
            } else {
                $this->accessDecisionManager->setVoters([]);
            }
        } else {
            $this->accessDecisionManager->setVoters($this->voters['untagged']);
        }

        return $this->accessDecisionManager->decide($token, $attributes, $object);
    }
}