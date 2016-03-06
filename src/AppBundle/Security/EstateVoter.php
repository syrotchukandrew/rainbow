<?php

namespace AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\Estate;

class EstateVoter extends Voter
{
    const VIEW = 'view';
    const CREATE = 'create';
    const EDIT = 'edit';
    const REMOVE = 'remove';

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::CREATE, self::EDIT, self::REMOVE))) {
            return false;
        }

        if (!$subject instanceof Estate) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        /** @var Estate */
        $estate = $subject;

        if (!$user instanceof UserInterface) {
            return false;
        }
        switch ($attribute) {
            case self::VIEW:
                if ($this->decisionManager->decide($token, array('ROLE_ADMIN', 'ROLE_MANAGER'))) {
                    return true;
                }
                break;
            case self::CREATE:
                if ($this->decisionManager->decide($token, array('ROLE_ADMIN', 'ROLE_MANAGER'))) {
                    return true;
                }
                break;
            case self::EDIT:
                if ($user->getUsername() === $estate->getCreatedBy() ||
                    $this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
                    return true;
                }
                break;
            case self::REMOVE:
                if ($user->getUsername() === $estate->getCreatedBy() ||
                $this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
                    return true;
                }
                break;
        }
        return false;
    }
}