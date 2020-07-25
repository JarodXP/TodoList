<?php

declare(strict_types = 1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AnonymousUserHandler
{
    protected EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Gets the Anonymous user to be bound to orphan tasks
     */
    public function getAnonymousUser(): User
    {
        //Looks for a possibly existing anonymous user
        $anonymous = $this->entityManager->getRepository('App:User')->findOneBy(['email' => 'anonymous@anonymous.com']);

        //If it doesn't exists, creates one
        if (is_null($anonymous)) {
            $anonymous = $this->createAnonymousUser();
        }

        return $anonymous;
    }

    /**
     * Creates an Anonymous user in database to be bound to orphan tasks
     */
    private function createAnonymousUser()
    {
        $anonymous = new User();
        $anonymous->setUsername('anonymous')
                ->setEmail('anonymous@anonymous.com')
                ->setPlainPassword(substr(md5((string) rand()), 0, 7));

        $this->entityManager->persist($anonymous);
        $this->entityManager->flush();

        return $anonymous;
    }
}
