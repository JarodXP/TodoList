<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait ControllerUtilsTrait
{
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * Initiates the kernel and gets the user repository
     */
    protected static function getEntityRepo(string $entityClassName):EntityRepository
    {
        static::bootKernel();
        $manager = static::$container->get('doctrine.orm.default_entity_manager');
        return static::$container->get('doctrine.orm.container_repository_factory')->getRepository($manager, $entityClassName);
    }
    
    /**
     * Simulates an authenticated user
     */
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

    /**
     * Tests the route redirects to login page if user is not authenticated
     */
    public function assertsRedirectionToLoginWhenUserIsNotAuthenticated(string $route)
    {
        $this->urlGenerator = static::$container->get('router');

        //Avoid client to redirect in order to catch the Response before
        $this->client->followRedirects(false);
        
        $this->client->request('GET', $route);

        //Builds the absolute URL to compare with the location header
        $url = $this->urlGenerator->generate('login', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->assertResponseRedirects($url);
    }
}
