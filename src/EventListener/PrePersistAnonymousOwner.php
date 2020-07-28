<?php

declare(strict_types = 1);

namespace App\EventListener;

use App\Entity\Task;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class PrePersistAnonymousOwner extends AbstractAnonymousOwnerListener
{
    public function prePersist(Task $task, LifecycleEventArgs $args)
    {
        $this->handleEvent($task);
    }
}
