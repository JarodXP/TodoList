<?php

declare(strict_types = 1);

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPasswordHasher implements EventSubscriber
{
    protected UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        //Gets the entity from the Doctrine event
        $object = $args->getObject();

        //Checks if the entity is a User
        if (!$object instanceof User) {
            return;
        }

        $this->encodePassword($object);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        //Gets the entity from the Doctrine event
        $object = $args->getObject();

        //Checks if the entity is a User
        if (!$object instanceof User) {
            return;
        }

        $this->encodePassword($object);

        // necessary to force the update to see the change
        /*$em = $args->getObjectManager();
        $meta = $em->getClassMetadata(get_class($object));
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $object);*/
    }

    private function encodePassword($user)
    {
        //Checks the case where plain password is null and password is not null to prevent undesired password modification
        if (!(is_null($user->getPlainPassword()) && !is_null($user->getPassword()))) {
            
            //sets the encoded password
            $user->setPassword($this->encoder->encodePassword($user, $user->getPlainPassword()));
        }
    }
}
