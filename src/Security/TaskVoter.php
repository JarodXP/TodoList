<?php

declare(strict_types = 1);

namespace App\Security;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Voter to control access and edition to tasks.
 * If another attribute (action) is needed (ie: read only), please complete this voter.
 */
class TaskVoter extends Voter
{
    const EDIT = 'edit';

    protected Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    protected function supports(string $attribute, $subject)
    {
        if (!in_array($attribute, [self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Task) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        /** @var Task $task */
        $task = $subject;
        
        switch ($attribute) {
            case self::EDIT: return $this->canEdit($task, $user);
            break;

            default:
            break;
        }
    }

    protected function canEdit(Task $task, User $user)
    {
        if ($this->security->isGranted('ROLE_ADMIN') || $user == $task->getUser()) {
            return true;
        }

        return false;
    }
}
