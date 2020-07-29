<?php

declare(strict_types = 1);

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\EventListener\AbstractPasswordHasherListener;

class PrePersistPasswordHasher extends AbstractPasswordHasherListener
{
    public function prePersist(User $user, LifecycleEventArgs $args)
    {
        $this->encodePassword($user);
    }
}
