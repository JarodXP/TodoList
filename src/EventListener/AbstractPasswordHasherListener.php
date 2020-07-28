<?php

declare(strict_types = 1);

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AbstractPasswordHasherListener
{
    protected UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    protected function encodePassword(User $user)
    {
        //Checks the case where plain password is null and password is not null to prevent undesired password modification
        if (!(is_null($user->getPlainPassword()) && !is_null($user->getPassword()))) {
            
            //sets the encoded password
            $user->setPassword($this->encoder->encodePassword($user, $user->getPlainPassword()));
        }
    }
}
