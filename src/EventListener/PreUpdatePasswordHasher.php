<?php

declare(strict_types = 1);

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\EventListener\AbstractPasswordHasherListener;

class PreUpdatePasswordHasher extends AbstractPasswordHasherListener
{
    public function preUpdate(User $user, LifecycleEventArgs $args)
    {
        $this->encodePassword($user);
    }
}
