<?php

declare(strict_types = 1);

namespace App\EventListener;

use App\Entity\Task;
use Doctrine\ORM\Events;
use App\Service\AnonymousUserHandler;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class AnonymousUserListener implements EventSubscriber
{
    protected EntityManagerInterface $entityManager;
    protected AnonymousUserHandler $handler;

    public function __construct(EntityManagerInterface $entityManager, AnonymousUserHandler $handler)
    {
        $this->entityManager = $entityManager;
        $this->handler = $handler;
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
        $this->handleEvent($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->handleEvent($args);
    }

    private function handleEvent(LifecycleEventArgs $args)
    {
        //Gets the entity from the Doctrine event
        $object = $args->getObject();

        //Checks if the entity is a User
        if (!$object instanceof Task || !is_null($object->getUser())) {
            return;
        }

        $anonymous = $this->handler->getAnonymousUser();
        $object->setUser($anonymous);
    }
}
