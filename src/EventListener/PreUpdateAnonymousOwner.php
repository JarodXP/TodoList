<?php

declare(strict_types = 1);

namespace App\EventListener;

use App\Entity\Task;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class PreUpdateAnonymousOwner extends AbstractAnonymousOwnerListener
{
    public function preUpdate(Task $task, LifecycleEventArgs $args)
    {
        $this->handleEvent($task);
    }
}
