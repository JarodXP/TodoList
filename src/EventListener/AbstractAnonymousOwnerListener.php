<?php

declare(strict_types = 1);

namespace App\EventListener;

use App\Entity\Task;
use Doctrine\ORM\Events;
use App\Service\AnonymousUserHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class AbstractAnonymousOwnerListener
{
    protected EntityManagerInterface $entityManager;
    protected AnonymousUserHandler $handler;

    public function __construct(EntityManagerInterface $entityManager, AnonymousUserHandler $handler)
    {
        $this->entityManager = $entityManager;
        $this->handler = $handler;
    }

    protected function handleEvent(Task $task)
    {
        //Checks if the task has an owner
        if (!is_null($task->getUser())) {
            return;
        }

        $anonymous = $this->handler->getAnonymousUser();
        $task->setUser($anonymous);
    }
}
