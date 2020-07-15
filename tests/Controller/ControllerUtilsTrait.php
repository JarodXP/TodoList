<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use Doctrine\ORM\EntityRepository;

trait ControllerUtilsTrait
{
    /**
     * Initiates the kernel and gets the user repository
     */
    protected static function getEntityRepo(string $entityClassName):EntityRepository
    {
        static::bootKernel();
        $manager = static::$container->get('doctrine.orm.default_entity_manager');
        return static::$container->get('doctrine.orm.container_repository_factory')->getRepository($manager, $entityClassName);
    }
    
    protected function authenticateClient()
    {
        //Gets the repository and the authenticated user.
        //The RepoGetter method can't be used here as the $container property of the TestCase class
        //is not recognized and leads to an exception with the loginUser() method.
        $manager = static::$container->get('doctrine.orm.default_entity_manager');
        $userRepository = static::$container->get('doctrine.orm.container_repository_factory')->getRepository($manager, 'App:User');

        $authenticatedUser = $userRepository->findOneBy(['email' => 'unique@unique.com']);
        
        $this->client->loginUser($authenticatedUser);
    }
}
